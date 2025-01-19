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

    private const REQUIRED_COLUMNS = [16, 17, 2, 12, 10, 8, 0];


    public function collection(Collection $rows)
    {
        Log::info('Starting Excel import process. Total rows: ' . $rows->count());

        $previousSapId = null;
        $previousPrice = null;
        $consecutiveCount = 0;
        $firstNewRowAfterConsecutive = [];
        $firstNewRowAfterConsecutivex = [];

        foreach ($rows as $row) {
            if ($row[0] === null || $row[0] === ""){
                Log::warning("Skipping empty row.");
                continue;
            }

            Log::info("Processing row", $row->toArray());

            // If SAP ID is the same as the previous one, increment counter, else reset counter and remember the first new row
            $amount = (float) str_replace(',', '.', str_replace(' ', '', $row[2] ?? 0));

            if ($previousSapId === $row[0] && $previousPrice === $amount * -1) {
                $consecutiveCount++;
            } else {
                Log::info("New SAP ID detected: " . $row[0]);

                // SAP ID changed, remember first row of new ID
                $previousSapId = $row[0];
                $previousPrice = $row[3];
                $firstNewRowAfterConsecutivex = $firstNewRowAfterConsecutive ? $firstNewRowAfterConsecutive : [];
                $firstNewRowAfterConsecutive = $row ? $row->toArray() : [];

                if (!is_array($firstNewRowAfterConsecutivex)) {
                    Log::warning("Skipping because previous row was not an array. Value:", [$firstNewRowAfterConsecutivex]);
                    continue;
                }                
                if ($consecutiveCount === 1) {
                    if ($firstNewRowAfterConsecutivex[17] === null || $firstNewRowAfterConsecutivex[17] === "") {
                        Log::warning("Missing required date in row: ", $firstNewRowAfterConsecutivex);
                        continue;
                    }
    
                    $formattedDate = $this->formatDate($firstNewRowAfterConsecutivex[17]);
    
                    if (!$formattedDate || !$this->validateRequiredColumns($firstNewRowAfterConsecutivex)) {
                        Log::warning("Invalid formatted date or missing required columns.");
                        continue;
                    }
    
                    if (!$this->operationTypeExists()) {
                        Log::warning("Operation type ID does not exist. Skipping row.");
                        continue;
                    }

                    Log::info("Row passed validation, creating SAP operation...");
                    $this->createSapOperation($firstNewRowAfterConsecutivex, $formattedDate);
                    Log::info("SAP operation successfully created.");

                    continue;
    
                }
                $firstNewRowAfterConsecutive = $row;
                $consecutiveCount = 1; // Reset counter for new SAP ID
                Log::info("Consecutive count reset to 1.");
                continue;
            }

            if ($consecutiveCount === 3) {
                if ($row[17] === null || $row[17] === "") {
                    Log::warning("Skipping row due to missing date", $row->toArray());
                    continue;
               }
    
                $formattedDate = $this->formatDate($row[17]);
    
    
                if (!$formattedDate || !$this->validateRequiredColumns($row)) {
                    Log::warning("Skipping row due to invalid date or missing columns", $row->toArray());
                    continue;
                }
    
                if (!$this->operationTypeExists()) {
                    Log::warning("Operation type ID " . self::OPERATION_TYPE_ID . " does not exist. Skipping row.");
                    continue;
                }
    
                Log::info("Row is valid, attempting to create SAP operation.");
                $this->createSapOperation($row, $formattedDate);
                Log::info("SAP operation successfully created.");
                continue;
    
    
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
        if ($row[0] === null || $row[0] === "") return null;

        // Check if an SapOperation with the same sap_id already exists in the database
        $exists = SapOperation::where('sap_id', $row[0])->where('sum', $row[2])->exists();

        if ($exists) {
            Log::info("An operation with sap_id {$row[0]} already exists. Skipping row.");
            return null; // Skip this row as an operation with the same sap_id already exists
        }

        // Ensure an account is found or created based on the SAP ID before creating the SapOperation.
        $account = Account::firstOrCreate(['sap_id' => $row[8]]);

        // Determine operation_type_id based on whether $row[3] is negative
        $operationTypeId = $row[2] < 0 ? self::OPERATION_TYPE_ID : 6;

        if ($row[2] < 0){
            $row[2] *= (-1);
        }
        $amount = $row[2] ?? ''; // Ensure we are using the correct index for amount

        Log::info("🔍 Checking sum column: " . json_encode($amount));
        Log::info("🔍 Checking whole row: " . json_encode($row));
        try {
            $sapOperation = new SapOperation([
                'date' => $formattedDate,
                'sum' => (float) str_replace(',', '.', str_replace(' ', '', $amount)), // Ensure numeric conversion
                'title' => $row[9],
                'operation_type_id' => $operationTypeId, // Use determined operation type ID here
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
