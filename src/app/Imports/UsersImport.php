<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\OperationType;
use App\Models\SapOperation;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Importable;

class UsersImport implements ToModel, WithMultipleSheets
{
    use Importable;


    private $previousSapId = null;
    private $previousPrice = null;
    private $consecutiveCount = 0;
    private $firstNewRowAfterConsecutive = null;
    private $firstNewRowAfterConsecutivex = null;

    private const OPERATION_TYPE_ID = 1;
    private const EXCEL_DATE_OFFSET = 25569;
    private const SECONDS_IN_A_DAY = 86400;

    private const REQUIRED_COLUMNS = [17, 3, 12, 10, 8, 0];

    /**
     * Process a row from the Excel file and convert it into a SapOperation model.
     *
     * This method handles the conversion of each row from the Excel sheet into a
     * SapOperation model instance. It includes validation of required columns,
     * conversion of Excel date format to a standard date format, and handling
     * of operation type existence.
     *
     * @param array $row An array representing a single row from the Excel sheet.
     *                   The row data is expected to contain specific columns
     *                   like date, sum, title, etc., at predefined indices.
     *
     * @return ?SapOperation Returns an instance of SapOperation if the row is
     *                       processed successfully and meets all validation criteria.
     *                       Returns null if the row fails validation checks or
     *                       if any exception occurs during the process.
     *
     */
    public function model(array $row): ?SapOperation
    {

        Log::warning($row[0]);
        // If SAP ID is the same as the previous one, increment counter, else reset counter and remember the first new row
        if ($this->previousSapId === $row[0] && $this->previousPrice === $row[3] * -1) {

            $this->consecutiveCount++;

        } else {

            // When SAP ID changes, remember the first row of the new SAP ID if consecutive count > 0
            $this->previousSapId = $row[0];
            $this->previousPrice = $row[3];
            $this->firstNewRowAfterConsecutivex = $this->firstNewRowAfterConsecutive;
            $this->firstNewRowAfterConsecutive = $row;
            if (!is_array($this->firstNewRowAfterConsecutivex)) return null;
            if ($this->consecutiveCount === 1) {


                if ($this->firstNewRowAfterConsecutivex[17] === null || $this->firstNewRowAfterConsecutivex[17] === "") {
                    return null;
                }

                $formattedDate = $this->formatDate($this->firstNewRowAfterConsecutivex[17]);

                if (!$formattedDate || !$this->validateRequiredColumns($this->firstNewRowAfterConsecutivex)) {
                    return null;
                }

                if (!$this->operationTypeExists()) {
                    Log::warning("Operation type ID " . self::OPERATION_TYPE_ID . " does not exist. Skipping row.");
                    return null;
                }

                return $this->createSapOperation($this->firstNewRowAfterConsecutivex, $formattedDate);

            }
            $this->firstNewRowAfterConsecutive = $row;
            $this->consecutiveCount = 1; // Reset counter for new SAP ID
            return null;
        }

        if ($this->consecutiveCount === 3) {


            if ($row[17] === null || $row[17] === "") {
                return null;
            }

            $formattedDate = $this->formatDate($row[17]);


            if (!$formattedDate || !$this->validateRequiredColumns($row)) {
                return null;
            }

            if (!$this->operationTypeExists()) {
                Log::warning("Operation type ID " . self::OPERATION_TYPE_ID . " does not exist. Skipping row.");
                return null;
            }


            return $this->createSapOperation($row, $formattedDate);


        }
        return null;
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


    private function formatDate($excelDate): ?string
    {
        if ($excelDate === null || $excelDate === "") {
            return null;
        }

        $unixDate = ($excelDate - self::EXCEL_DATE_OFFSET) * self::SECONDS_IN_A_DAY;
        return gmdate("Y-m-d", $unixDate);
    }


    private function createSapOperation($row, $formattedDate): ?SapOperation
    {
        if ($row[0] === null || $row[0] === "") return null;

        // Check if an SapOperation with the same sap_id already exists in the database
        $exists = SapOperation::where('sap_id', $row[0])->where('sum', $row[3])->exists();

        if ($exists) {
            Log::info("An operation with sap_id {$row[0]} already exists. Skipping row.");
            return null; // Skip this row as an operation with the same sap_id already exists
        }

        // Ensure an account is found or created based on the SAP ID before creating the SapOperation.
        $account = Account::firstOrCreate(['sap_id' => $row[8]]);

        // Determine operation_type_id based on whether $row[3] is negative
        $operationTypeId = $row[3] < 0 ? self::OPERATION_TYPE_ID : 6;

        if ($row[3] < 0){
            $row[3] *= (-1);
        }
        $sapOperation = new SapOperation([
            'date' => $formattedDate,
            'sum' => $row[3],
            'title' => $row[12],
            'operation_type_id' => $operationTypeId, // Use determined operation type ID here
            'subject' => $row[10],
            'sap_id' => $row[0],
            'account_sap_id' => $row[8],
        ]);

        try {
            $sapOperation->save();
            return $sapOperation;
        } catch (Exception $e) {
            Log::error("Error saving SapOperation: " . $e->getMessage());
            return null;
        }
    }


    /**
     * @return array
     */
    public function sheets(): array
    {
        // Only import the first sheet
        return [
            0 => new self(),
        ];
    }


}
