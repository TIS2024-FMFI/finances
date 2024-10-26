<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\DatabaseException;
use App\Exceptions\StorageException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\FileHelper;
use App\Http\Requests\FinancialOperations\UpdateOperationRequest;
use App\Models\FinancialOperation;
use App\Models\Lending;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

/**
 * Manages updates of financial operations, including checking and unchecking.
 */
class UpdateOperationController extends GeneralOperationController
{
    /**
     * Prepares the data necessary to populate the form handling operation updates.
     *
     * @param FinancialOperation $operation
     * the operation that is about to be updated
     * @return array
     * an array containing information about the operation itself and the supported
     * operation types
     */
    public function getFormData(FinancialOperation $operation)
    {
        return ['operation' => $operation];
    }

    /**
     * Handles the request to update a financial operation.
     *
     * @param FinancialOperation $operation
     * the operation to be updated
     * @param UpdateOperationRequest $request
     * the HTTP request to update the operation
     * @return Application|ResponseFactory|Response
     */
    public function update(FinancialOperation $operation, UpdateOperationRequest $request)
    {
        $requestData = $request->validated();

        if (!$this->validateUpdate($operation, $requestData))
        {
            Log::debug('Update not validated.');
            return response(trans('financial_operations.update.failure'), 500);
        }
            
        try {
            $newAttachment = $this->saveAttachment($operation->account(), $requestData);
            $oldAttachment = $operation->attachment;

            $this->updateOperationWithinTransaction(
                $operation, $requestData, $oldAttachment, $newAttachment
            );
        } catch (Exception $e) {
            Log::debug('Updating financial operation failed, error: {e}', ['e' => $e]);
            if ($e instanceof ValidationException)
                throw $e;
            return response(trans('financial_operations.update.failure'), 500);
        }

        return response(trans('financial_operations.update.success'));
    }

    /**
     * Runs a database transaction in which a financial operation is updated.
     *
     * @param FinancialOperation $operation
     * the operation to be updated
     * @param array $data
     * the updated operation data
     * @param string|null $oldAttachment
     * path to the operation's original attachment file (if there was one)
     * @param string|null $newAttachment
     * path to the operation's updated attachment file (if there is one)
     * @throws Exception
     */
    private function updateOperationWithinTransaction(
        FinancialOperation $operation, array $data,
        string|null $oldAttachment, string|null $newAttachment
    ) {
        $updateOperationTransaction = new DBTransaction(
            fn () => $this->updateOperationRecordAndDeleteOldAttachment(
                $operation, $data, $oldAttachment, $newAttachment
            ),
            fn () => FileHelper::deleteFileIfExists($newAttachment)
        );

        $updateOperationTransaction->run();
    }

    /**
     * Updates the operation, creating or deleting attachment files and associated
     * lending records if needed.
     *
     * @param FinancialOperation $operation
     * the operation to be updated
     * @param array $data
     * the updated operation data
     * @param string|null $oldAttachment
     * path to the operation's original attachment file (if there was one)
     * @param string|null $newAttachment
     * path to the operation's updated attachment file (if there is one)
     * @throws DatabaseException
     * @throws StorageException
     */
    private function updateOperationRecordAndDeleteOldAttachment(
        FinancialOperation $operation, array $data,
        string|null $oldAttachment, string|null $newAttachment
    ) {
        $this->updateOperationRecord($operation, $data, $newAttachment);

        $operation->refresh();

        if ($operation->isLending()) {
            $this->upsertLending($operation, $data);
            $this->updateRepaymentRecord($operation->lending, $data);
        }

        if ($newAttachment)
            FileHelper::deleteFileIfExists($oldAttachment);
    }

    /**
     * Updates the financial operation's record in the database.
     *
     * @param FinancialOperation $operation
     * the operation to be updated
     * @param array $data
     * the updated operation data
     * @param string|null $newAttachment
     * path to the operation's updated attachment file (if there is one)
     * @throws DatabaseException
     */
    private function updateOperationRecord(
        FinancialOperation $operation, array $data, string|null $newAttachment
    ) {
        $recordData = $data;

        if ($newAttachment)
            $recordData['attachment'] = $newAttachment;
        else
            unset($recordData['attachment']);

        if (!$operation->update($recordData))
            throw new DatabaseException('The operation wasn\'t updated.');
    }

    /**
     * Updates the record of the repayment associated with the given lending if
     * such a repayment exists.
     *
     * @param Lending $lending
     * the lending whose repayment to update
     * @param array $data
     * the updated operation data of lending
     * @throws DatabaseException
     */
    private function updateRepaymentRecord(Lending $lending, array $data)
    {
        $repayment = $lending->repayment;

        if (!$repayment)
            return;

        $recordData = $data;
        unset($recordData['date'], $recordData['attachment']);

        if (!$repayment->operation->update($recordData))
            throw new DatabaseException('The repayment wasn\'t updated.');
    }

    /**
     * Validates an attempt to update an operation.
     *
     * @param FinancialOperation $operation
     * the operation to be updated
     * @param array $data
     * the updated operation data
     * @return bool
     * true if the update is valid, false otherwise
     */
    private function validateUpdate(FinancialOperation $operation, array $data)
    {
        if ($operation->isRepayment())
            return false;

        if ($operation->isLending() && array_key_exists('checked', $data))
            return false;

        return true;
    }
}
