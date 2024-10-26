<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\DatabaseException;
use App\Exceptions\StorageException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\FileHelper;
use App\Models\FinancialOperation;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

/**
 * Manages deletion of financial operations.
 */
class DeleteOperationController extends GeneralOperationController
{
    /**
     * Handles the request to delete a financial operation.
     *
     * @param FinancialOperation $operation
     * the operation to be deleted
     * @return Application|ResponseFactory|Response
     * a response containing information about this operation's result
     */
    public function delete(FinancialOperation $operation)
    {
        try {
            $this->deleteOperationWithinTransaction($operation);
        } catch (Exception $e) {
            return response(trans('financial_operations.delete.failure'), 500);
        }
        return response(trans('financial_operations.delete.success'));
    }

    /**
     *  Runs a database transaction in which a financial operation is deleted.
     *
     * @param FinancialOperation $operation
     * the operation to be deleted
     * @throws Exception
     */
    private function deleteOperationWithinTransaction(FinancialOperation $operation)
    {
        $deleteOperationTransaction = new DBTransaction(
            fn () => $this->deleteOperation($operation)
        );

        $deleteOperationTransaction->run();
    }

    /**
     * Deletes the operation.
     * Also deletes its attachment file and its repayment, if it has any of those.
     *
     * @param FinancialOperation $operation
     * the operation to be deleted
     * @throws DatabaseException|StorageException
     */
    private function deleteOperation(FinancialOperation $operation)
    {
        $attachment = $operation->attachment;
        if ($operation->isLending())
            $operation->lending->deleteRepayment();
        if (! $operation->delete())
            throw new DatabaseException('The operation wasn\'t deleted.');

        FileHelper::deleteFileIfExists($attachment);
    }
}
