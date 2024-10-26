<?php

namespace App\Http\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\FileFormatException;
use App\Exceptions\StorageException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\SapReportParser;
use App\Imports\UsersImport;
use App\Models\SapReport;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Account;
use Maatwebsite\Excel\Facades\Excel;

// Imports the Account model

class ExcelImportController extends Controller
{
    /**
     * Handle the upload of an Excel file.
     *
     * @param Request $request
     * @param Account $account // Use Account instead of User
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function upload(Request $request, Account $account): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $report = $request->file('excel_file');

        try {
            Excel::import(new UsersImport, $report);


        } catch (Exception $e) {
            Log::error("Error saving SapOperation: " . $e->getMessage());
            return response(trans('sap_reports.upload.failed'), 401);
        }

        try {
            $this->storeFile($report, $account);
        } catch (Exception $e) {

            return response(trans('sap_reports.upload.failed'), 500);
        }

        return response(trans('sap_reports.upload.success'), 201);
    }

    /**
     * Store the uploaded file in storage in an account-specific directory.
     *
     * @param UploadedFile $file
     * @param Account $account // Use Account instead of User
     * @throws Exception
     */
    private function storeFile(UploadedFile $file, Account $account): void
    {
        $reportPath = $this->saveReportFileToUserStorage($account, $file);

        $absoluteReportPath = Storage::path($reportPath);
        $exportedOrUploadedOn = $this->getDateExportedOrToday($absoluteReportPath);

        // Process the Excel file
        $createRecordTransaction = new DBTransaction(
            fn() => $this->createReportRecord($account, $reportPath),
            fn() => Storage::delete($reportPath)
        );

        $createRecordTransaction->run();
    }

    /**
     * Save the uploaded report file to the user's storage directory.
     *
     *
     * @param User $user The user to whom the storage directory belongs.
     * @param UploadedFile $report The uploaded report file.
     *
     * @return string The path where the report file is saved.
     *
     * @throws StorageException If the file cannot be saved to the storage.
     */

    private function saveReportFileToUserStorage(Account $account, UploadedFile $report): string
    {
        $reportsDirectoryPath = SapReport::getExcelReportsDirectoryPath($account);
        $reportPath = Storage::putFile($reportsDirectoryPath, $report);

        if (!$reportPath) {
            throw new StorageException('File not saved.');
        }

        return $reportPath;
    }

    /**
     * Create a record in the database for the uploaded report.
     *
     * @param Account $account The account to which the report is associated.
     * @param string $reportPath The storage path of the uploaded report.
     *
     * @throws DatabaseException If the record cannot be created in the database.
     */
    private function createReportRecord(Account $account, string $reportPath): void
    {
        $absoluteReportPath = Storage::path($reportPath);
        $exportedOrUploadedOn = $this->getDateExportedOrToday($absoluteReportPath);

        $report = $account->sapReports()->create([
            'path' => $reportPath,
            'exported_or_uploaded_on' => $exportedOrUploadedOn,
        ]);

        if (!$report->exists) {
            throw new DatabaseException('Record not saved.');
        }
    }

    /**
     * Get the date when the report was exported or the current date.
     *
     * @param string $reportPath The storage path of the report.
     *
     * @return \Illuminate\Support\Carbon|\Carbon\Traits\Creator The date when the report was exported or the current date.
     */
    private function getDateExportedOrToday(string $reportPath): \Illuminate\Support\Carbon|\Carbon\Traits\Creator
    {

        return now();

    }


}
