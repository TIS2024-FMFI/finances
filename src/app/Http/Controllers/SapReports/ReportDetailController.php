<?php

namespace App\Http\Controllers\SapReports;

use App\Http\Controllers\Controller;
use App\Http\Helpers\FileHelper;
use App\Http\Requests\SapReports\DownloadReportRequest;
use App\Models\SapReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * A controller responsible for providing details about an existing SAP report.
 *
 * This controller provides methods to:
 *      - download a SAP report
 */
class ReportDetailController extends Controller
{
    /**
     * Handle a request to download a SAP report.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * the view that will be shown
     */
    public function download(SapReport $report)
    {
        $path = $report->path;
        $fileName = $report->generateReportFileName();

        return FileHelper::downloadFileIfExists($path, $fileName);
    }
}
