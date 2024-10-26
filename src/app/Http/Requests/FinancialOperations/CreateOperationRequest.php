<?php

namespace App\Http\Requests\FinancialOperations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * A request to create a new financial operation.
 * 
 * Fields: title, date, operation_type_id, subject, sum, attachment,
 *         expected_date_of_return.
 */
class CreateOperationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['required', 'max:255'],
            'date' => ['required', 'date'],
            'operation_type_id' => ['required', 'numeric', 'exists:operation_types,id'],
            'subject' => ['required', 'max:255'],
            'sum' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', File::types(['txt','pdf', 'doc', 'docx', 'zip'])],
            'expected_date_of_return' => ['nullable', 'date', 'after_or_equal:date'],
        ];
    }
}
