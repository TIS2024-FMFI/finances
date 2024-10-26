<?php

namespace App\Http\Requests\FinancialOperations;

use App\Models\FinancialOperation;
use App\Models\OperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Log;


/**
 * A request to create a new repayment operation.
 *
 * Fields: date.
 */
class CreateRepaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'date' => ['required', 'date'],
        ];
    }

    /**
     * Prepare operation data for this repayment based on the loan with which
     * the repayment will be associated.
     *
     * @param FinancialOperation $loan
     * the loan with which the repayment will be associated
     * @return array
     * the operation data
     */
    public function prepareValidatedOperationData(FinancialOperation $loan)
    {
        $repaymentType = ($loan->isExpense())

            ? OperationType::getRepaymentIncome()
            : OperationType::getRepaymentExpense();
        return [
            'title' => $loan->title,
            'date' => $this->validated('date'),
            'operation_type_id' => $repaymentType->id,
            'subject' => $loan->subject,
            'sum' => $loan->sum,
            'previous_lending_id' => $loan->id,
        ];
    }
}
