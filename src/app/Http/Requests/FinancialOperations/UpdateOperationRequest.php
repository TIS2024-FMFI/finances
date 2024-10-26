<?php

namespace App\Http\Requests\FinancialOperations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * A request to update an existing financial operation.
 *
 * Fields: title, date, subject, sum, attachment, expected_date_of_return.
 */
class UpdateOperationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['filled', 'max:255'],
            'date' => ['filled', 'date'],
            'subject' => ['filled', 'max:255'],
            'sum' => ['filled', 'numeric', 'min:0'],
            'attachment' => ['nullable', File::types(['txt','pdf', 'doc', 'docx', 'zip'])],
            'checked' => ['nullable', 'boolean'],
            'sap_operation_id' => ['nullable', 'numeric', 'exists:sap_operations,id'],
            'expected_date_of_return' => ['nullable', 'date', 'after_or_equal:date']
        ];
    }
}
