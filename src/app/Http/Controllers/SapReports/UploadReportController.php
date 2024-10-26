<?php

namespace App\Http\Controllers\SapReports;

use App\Exceptions\DatabaseException;
use App\Exceptions\FileFormatException;
use App\Exceptions\StorageException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\SapReportParser;
use \Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\SapReports\UploadReportRequest;
use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\Print_;
use Psy\Readline\Hoa\Console;

/**
 * A controller responsible for uploading new SAP reports.
 *
 * This controller provides methods to:
 *      - upload a SAP report
 */
class UploadReportController extends Controller
{
    /**
     * Handle a request to upload a SAP report.
     *
     * @param \App\Http\Requests\SapReports\UploadReportRequest $request
     * the request containing the SAP report file and the id of an account with
     * which to associate the report
     * @param \App\Models\Account $account
     * the account with which to associate the report
     * @return \Illuminate\Http\Response
     * a response containing the information about the result of this operation
     * presented as a plain-text message
     */
    public function upload(UploadReportRequest $request, Account $account)
    {
        $report = $request->file('sap_report');

        try {
            $this->uploadReportWithinTransaction($account, $report);
        } catch (Exception $e) {

            return response(trans('sap_reports.upload.failed'), 500);
        }

        return response(trans('sap_reports.upload.success'), 201);
    }

    /**
     * Upload a SAP report.
     *
     * @param \App\Models\Account $account
     * the account with which to associate the report
     * @param \Illuminate\Http\UploadedFile $report
     * the SAP report file to upload
     * @throws \Exception
     * thrown if an error occurred
     * @return void
     */
    private function uploadReportWithinTransaction(Account $account, UploadedFile $report)
    {
        $reportPath = $this->saveReportFileToUserStorage($account, $report);

        $createRecordTransaction = new DBTransaction(
            fn () => $this->createReportRecord($account, $reportPath),
            fn () => Storage::delete($reportPath)
        );

        $createRecordTransaction->run();
    }

    /**
     * Save a SAP report to the storage reserved for a user.
     *
     * @param \App\Models\Account $account
     * the user under which to save the report
     * @param \Illuminate\Http\UploadedFile $report
     * the SAP report file to upload
     * @return string
     * the path to the saved SAP report file
     */
    private function saveReportFileToUserStorage(Account $account, UploadedFile $report)
    {
        $reportsDirectoryPath = SapReport::getExcelReportsDirectoryPath($account);
        $reportPath = Storage::putFile($reportsDirectoryPath, $report);

        if (!$reportPath) {
            throw new StorageException('File not saved.');
        }

        return $reportPath;
    }

    /**
     * Create and persist a SAP Report model representing the saved SAP report
     * file.
     *
     * @param \App\Models\Account $account
     * the account with which to associate the report
     * @param string $reportPath
     * the path to the saved SAP report file
     * @throws \App\Exceptions\DatabaseException
     * thrown if the SAP Report model could not be persisted
     * @return void
     */
    private function createReportRecord(Account $account, string $reportPath)
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
     * Get the date the SAP report was exported or today if no information about
     * the export date can be found.
     *
     * @param string $reportPath
     * the path to the SAP report file
     * @return \Carbon\Traits\Creator|\Illuminate\Support\Carbon
     */
    private function getDateExportedOrToday(string $reportPath)
    {
        try {

            $reportParser = new SapReportParser($reportPath);

            return $reportParser->getDateExported();
        } catch (FileNotFoundException|FileFormatException $e) {
            return now();
        }
    }
}
