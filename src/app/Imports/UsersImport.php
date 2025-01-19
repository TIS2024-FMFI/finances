<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\OperationType;
use App\Models\SapOperation;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMapping;


class UsersImport implements ToCollection, WithCalculatedFormulas, WithMapping, WithMultipleSheets
{
    use Importable;

    private $sheetName;

    public function __construct($sheetName = 'RawData') // Define sheet
    {
        $this->sheetName = $sheetName;
    }

    public function sheets(): array
    {
        return [
            $this->sheetName => new self($this->sheetName), // Only import the specified sheet
        ];
    }

    private const OPERATION_TYPE_ID = 1;
    private const EXCEL_DATE_OFFSET = 25569;
    private const SECONDS_IN_A_DAY = 86400;

    private const REQUIRED_COLUMNS = [16, 17, 2, 12, 10, 8, 0, 4];


    public function collection(Collection $rows)
    {
        Log::info('Starting Excel import process. Total rows: ' . $rows->count());

        $previousSapId = null;
        $previousHKAccount = null;
        $prevousFinanceItem = null;
        $firstRowProcessed = false;

        foreach ($rows as $row) {
            if (!$firstRowProcessed){
                $firstRowProcessed = true;
                continue;
            }
            if ($row[0] === null || $row[0] === ""){
                Log::warning("Skipping empty row.");
                continue;
            }

            Log::info("Processing row", $row->toArray());

            if ($previousSapId === $row[0] && $previousHKAccount === $row[16] && $prevousFinanceItem === $row[4]) {
                continue;
            } else {
                Log::info("New SAP ID detected: " . $row[0]);

                $previousSapId = $row[0];
                $previousHKAccount = $row[16];
                $prevousFinanceItem = $row[4];
                
                if ($row[17] === null || $row[17] === "") {
                    Log::warning("Missing required date in row: ", $row);
                    continue;
                }

                $formattedDate = $this->formatDate($row[17]);

                if (!$formattedDate || !$this->validateRequiredColumns($row)) {
                    Log::warning("Invalid formatted date or missing required columns.");
                    continue;
                }

                if (!$this->operationTypeExists()) {
                    Log::warning("Operation type ID does not exist. Skipping row.");
                    continue;
                }

                Log::info("Row passed validation, creating SAP operation...");
                $this->createSapOperation($row, $formattedDate);
                Log::info("SAP operation successfully created.");    
            }
        }
    }

    public function map($row): array
    {
        return array_map(fn($cell) => (string) $cell, $row); // Convert everything to a string
    }

    private function operationTypeExists(): bool
    {
        return OperationType::find(self::OPERATION_TYPE_ID) !== null;
    }

    private function validateRequiredColumns($row): bool
    {
        foreach (self::REQUIRED_COLUMNS as $index) {
            if (!isset($row[$index]) || $row[$index] === null) {
                Log::warning("Required column at index $index is null. Skipping row.");
                return false;
            }
        }
        return true;
    }


    private function formatDate($date)
    {    
        if (!$date || trim($date) === '') {
            return null;
        }
    
        try {
            // Convert Excel numeric date to PHP date
            if (is_numeric($date)) {
                $formattedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            } else {
                $formattedDate = date('Y-m-d', strtotime(str_replace('.', '-', $date)));
            }
    
            return $formattedDate;
        } catch (\Exception $e) {
            return null;
        }
    }
    


    private function createSapOperation($row, $formattedDate): ?SapOperation
    {
        // Check if an SapOperation with the same sap_id already exists in the database
        $exists = SapOperation::where('sap_id', $row[0])->where('sum', $row[2])->exists();

        if ($exists) {
            Log::info("An operation with sap_id {$row[0]} already exists. Skipping row.");
            return null;
        }

        // Ensure an account is found or created based on the SAP ID before creating the SapOperation.
        Account::firstOrCreate(['sap_id' => $row[8]]);

        // Determine operation_type_id based on whether $row[2] is negative
        $amount = (float) str_replace(',', '.', str_replace(' ', '', $row[2] ?? 0));
        $operationTypeId = $amount < 0 ? 6 : self::OPERATION_TYPE_ID;
        Log::info("Operation type ID: " . $operationTypeId . " for amount: " . $amount);


        if ($amount < 0){
            $amount *= (-1);
        }
        Log::info("🔍 Checking whole row: " . json_encode($row));
        try {
            $sapOperation = new SapOperation([
                'date' => $formattedDate,
                'sum' => $amount,
                'title' => $row[9],
                'operation_type_id' => $operationTypeId,
                'subject' => $row[11],
                'sap_id' => $row[0],
                'account_sap_id' => $row[8],
            ]);

            $sapOperation->save();

            Log::info("SAP operation saved successfully.");
            return $sapOperation;
        } catch (Exception $e) {
            Log::error("Error saving SapOperation: " . $e->getMessage());
            return null;
        }
    }
}
