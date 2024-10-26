<?php

namespace App\Http\Controllers\SapOperations;

use App\Http\Controllers\Controller;
use App\Models\FinancialOperation;
use App\Models\SapOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Manages the functionality of the 'operation detail' modal.
 */
class SapOperationCheckController extends Controller
{
    /**
     * Gets the unchecked financial operations' data
     *
     * @return array
     * an array containing the unchecked financial operations collection
     */
    public function getFormData(SapOperation $operation)
    {
        Log::debug('Getting form data for checking a SAP operation');
        $financialOperations = FinancialOperation::whereNull('sap_operation_id')->get();
        $operations = collect([]);
        foreach($financialOperations as $finOp)
        {
            if ($finOp->account()->sap_id === $operation->account_sap_id)
            {
                Log::debug('This op is all right {e}', ['e' => $finOp]);
                $operations->push([$finOp, $finOp->user()]);
            }
        }
        Log::debug('All fin ops that have not been checked {e}', ['e' => $operations]);
        return ['uncheckedOperations' => $operations];
    }

    public function getUncheckData(SapOperation $operation)
    {
        Log::debug('Getting form data for unchecking a SAP operation');
        $financial_operation = $operation->financialOperation;
        Log::debug('Sap op gettin unchecked {e}', ['e' => $operation]);
        Log::debug('Fin op gettin unchecked {e}', ['e' => $financial_operation]);
        return ['operation' => $financial_operation];
    }

    public function checkOperation(Request $request, SapOperation $operation)
    {
        Log::debug('Sap op to be checked {e}', ['e'=>$operation]);
        $financial_operation_id = $request->collect()['checked_op_id'];
        Log::debug('Fin op id requested to be paired {e}', ['e'=>$financial_operation_id]);
        $financial_operation = FinancialOperation::where('id', $financial_operation_id)->first();
        Log::debug('Fin op requested to be paired {e}', ['e'=>$financial_operation]);
        $financial_operation->sap_operation_id = $operation->id;
        $financial_operation->save();
        if (! $operation->isChecked())
        {
            return response(trans('financial_operations.check.failure'), 500);
        }
        return response(trans('financial_operations.check.success'), 201);
    }

    public function uncheckOperation(Request $request, SapOperation $operation)
    {
        $financial_operation = FinancialOperation::where('sap_operation_id', $operation->id)->first();
        $financial_operation->sap_operation_id = null;
        $financial_operation->save();
        if ($operation->isChecked())
        {
            return response(trans('financial_operations.check.failure'), 500);
        }
        return response(trans('financial_operations.check.success'), 201);
    }
}

