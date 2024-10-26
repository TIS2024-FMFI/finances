<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\DatabaseException;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\Lending;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

/**
 * Parent class containing general functions useful for both 'create operation' and 'update operation' controllers.
 */
class GeneralOperationController extends Controller
{

    /**
     * Saves an attachment file from request data to user-specific storage.
     *
     * This method checks for an attachment in the request data and saves it to a storage location based on the account's associated user or the currently authenticated user. It's designed to handle file attachments for financial operations, ensuring they are stored securely and associated with the correct user account.
     *
     * @param Account $account The account related to the financial operation.
     * @param array $requestData Data from the request, expected to contain an 'attachment' key with the file.
     * @return string|null Path to the saved file, or null if no attachment is present.
     */

    protected function saveAttachment(Account $account, array $requestData)
    {
        if (array_key_exists('attachment', $requestData)) {
            $file = $requestData['attachment'];
            if ($file) {
                // Skontrolujte, či $account->user->first() vráti užívateľa, inak použije aktuálne prihláseného užívateľa
                $user = $account->user->first() ?? Auth::user();
                return $this->saveAttachmentToUserStorage($user, $file);
            }
        }
        return null;
    }


    /**
     * Saves the given file as an attachment for a financial operation.
     *
     * @param $user
     * the user to which the financial operation belongs
     * @param $file
     * the file to be saved
     * @return string
     * the path to the saved file
     */
    private function saveAttachmentToUserStorage(User $user, UploadedFile $file)
    {
        $dir = FinancialOperation::getAttachmentsDirectoryPath($user);
        return Storage::putFile($dir, $file);
    }

    /**
     * Inserts a lending record related to a financial operation into the database.
     * If that operation already has a lending record, the lending is instead
     * updated with the new data.
     *
     * @param FinancialOperation $operation
     * the operation associated with the lending
     * @param array $lendingData
     * the data based on which to create or update the lending
     * @return void
     * @throws DatabaseException
     */
    protected function upsertLending(FinancialOperation $operation, array $lendingData)
    {
        $validatedData = $this->getValidatedLendingData($operation, $lendingData);

        $lending = Lending::updateOrCreate(
            ['id' => $operation->id],
            $validatedData
        );

        if (!$lending->exists)
            throw new DatabaseException('The lending wasn\'t created.');
    }

    /**
     * Validates lending data.
     *
     * @param FinancialOperation $operation
     * the operation associated with the lending
     * @param array $lendingData
     * the lending data to validate
     * @return array
     * the validated data
     * @throws ValidationException
     */
    private function getValidatedLendingData(
        FinancialOperation $operation, array $lendingData
    ) {
        return ($operation->isRepayment())
            ? $this->getValidatedRepaymentData($operation, $lendingData)
            : $this->getValidatedLoanData($operation, $lendingData);
    }

    /**
     * Validates repayment data.
     *
     * @param FinancialOperation $operation
     * the operation associated with the lending
     * @param array $repaymentData
     * the repayment data to validate
     * @return array
     * the validated data
     * @throws ValidationException
     */
    private function getValidatedRepaymentData(
        FinancialOperation $operation, array $repaymentData
    ) {
        $loan = FinancialOperation::findOrFail(
            $repaymentData['previous_lending_id']
        );

        $this->validateLendingDates($loan, $operation);

        return ['previous_lending_id' => $repaymentData['previous_lending_id']];
    }

    /**
     * Validates loan data.
     *
     * @param FinancialOperation $operation
     * the operation associated with the lending
     * @param array $loanData
     * the loan data to validate
     * @return array
     * the validated data
     * @throws ValidationException
     */
    private function getValidatedLoanData(
        FinancialOperation $operation, array $loanData
    ) {
        $repaymentLending = Lending::findRepayment($operation->id);
        $repayment = ($repaymentLending) ? $repaymentLending->operation : null;

        $this->validateLendingDates($operation, $repayment);

        return ['expected_date_of_return' => $loanData['expected_date_of_return']];
    }

    /**
     * Ensures that a loan is not repaid earlier than provided.
     *
     * @param FinancialOperation $loan
     * the loan to consider
     * @param FinancialOperation|null $repayment
     * the repayment to check (if any)
     * @return void
     * @throws ValidationException
     */
    private function validateLendingDates(
        FinancialOperation $loan, FinancialOperation|null $repayment
    ) {
        if ($loan && $repayment && $repayment->date->lt($loan->date))
            throw ValidationException::withMessages([
                'date' => trans('financial_operations.repayment_date_invalid')
            ]);
    }
}
