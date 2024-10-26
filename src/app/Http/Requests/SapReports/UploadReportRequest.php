<?php

namespace App\Http\Requests\SapReports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * A request upload a new SAP report.
 *
 * Fields: sap_report.
 */
class UploadReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'sap_report' => ['required', File::types(['txt', 'xls', 'xlsx'])],
        ];
    }
}
