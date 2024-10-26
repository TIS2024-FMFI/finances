<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Http\Controllers\Controller;
use App\Models\FinancialOperation;
use App\Models\SapOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Manages the functionality of the 'operation detail' modal.
 */
class OperationCheckController extends Controller
{
    /**
     * Gets the unchecked SAP operations' data
     *
     * @return array
     * an array containing the unchecked SAP operations collection
     */
    public function getFormData(FinancialOperation $operation)
    {
        Log::debug('Getting form data for checking a fin. operation');
        $checkedOperations = FinancialOperation::whereNotNull('sap_operation_id')->pluck('sap_operation_id');
        Log::debug('All fin ops that have been checked {e}', ['e' => $checkedOperations]);
        $operations = SapOperation::whereNotIn('id', $checkedOperations)->get();

        //Log::debug('All sap ops that have yet not been checked {e}', ['e' => $operations]);
        return ['uncheckedOperations' => $operations];
    }

    public function getUncheckData(FinancialOperation $operation)
    {
        Log::debug('Getting form data for unchecking a fin. operation');
        $sap_operation = $operation->sapOperation;
        Log::debug('Fin op gettin unchecked {e}', ['e' => $operation]);
        Log::debug('Sap op gettin unchecked {e}', ['e' => $sap_operation]);
        return ['operation' => $sap_operation];
    }

    public function checkOperation(Request $request, FinancialOperation $operation)
    {
        Log::debug('Fin op to be checked {e}', ['e'=>$operation]);
        $sap_operation_id = $request->collect()['checked_op_id'];
        Log::debug('Sap op id {e}', ['e'=>$sap_operation_id]);
        $operation->sap_operation_id = $sap_operation_id;
        $operation->save();
        if (! $operation->isChecked())
        {
            return response(trans('financial_operations.check.failure'), 500);
        }
        return response(trans('financial_operations.check.success'), 201);
    }

    public function uncheckOperation(Request $request, FinancialOperation $operation)
    {
        $operation->sap_operation_id = null;
        $operation->save();
        if ($operation->isChecked())
        {
            return response(trans('financial_operations.check.failure'), 500);
        }
        return response(trans('financial_operations.check.success'), 201);
    }
}
