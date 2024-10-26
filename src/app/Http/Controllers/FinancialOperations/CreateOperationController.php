<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\DatabaseException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\FileHelper;
use App\Http\Requests\FinancialOperations\CreateOperationRequest;
use App\Http\Requests\FinancialOperations\CreateRepaymentRequest;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\Lending;
use App\Models\OperationType;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * Manages creation of financial operations.
 */
class CreateOperationController extends GeneralOperationController
{
    /**
     * Retrieves form data necessary for creating a financial operation.
     *
     * This method determines the form data required when a user is attempting to create a new financial operation.
     * It differentiates between admin and non-admin users, providing each with the appropriate set of data based on their roles.
     * Admin users are granted access to all user-assignable operation types and all unrepaid lendings across the platform,
     * while non-admin users receive data specifically related to their association with the account in question.
     *
     * @param Account $account The account for which the form data is being prepared. This account context is used to tailor the returned data for non-admin users, ensuring they only see options relevant to their permissions and associations.
     * @return array An array containing two key pieces of information: 'operation_types' and 'unrepaid_lendings'.
     *      - 'operation_types': A collection of all operation types that a user can assign when creating a new financial operation. This ensures users are presented with valid options that reflect the types of operations supported by the system.
     *      - 'unrepaid_lendings': A collection of all unrepaid lending operations associated with the account. For admin users, this includes all unrepaid lendings across the platform, while for non-admin users, it is limited to those associated with their account through the 'account_user_id'.
     *
     * The differentiation in data provided to admin versus non-admin users allows for a flexible and secure approach to operation creation, ensuring users can only interact with data and operations relevant to their role and permissions.
     */

    public function getFormData(Account $account)
    {
        // Check if the authenticated user is an admin
        if (Auth::user()->is_admin) {
            // Admin users get all user-assignable operation types and all unrepaid lendings associated with the account
            return [
                'operation_types' => OperationType::userAssignable()->get(),
                'unrepaid_lendings' => FinancialOperation::unrepaidLendings()->get() //môžme upraviť eštex
            ];
        } else {
            // Non-admin users get data based on their specific association with the account
            $user = $account->user->first();
            return [
                'operation_types' => OperationType::userAssignable()->get(),
                'unrepaid_lendings' => FinancialOperation::unrepaidLendings()->where('account_user_id', '=', $user->pivot->id)->get()
            ];
        }
    }


    /**
     * Handles the request to create a new financial operation.
     *
     * @param Account $account
     * the account with which to associate the operation
     * @param CreateOperationRequest $request
     * the request to create the operation
     * @return Application|ResponseFactory|Response
     * a response containing information about this operation's result
     */
    public function create(Account $account, CreateOperationRequest $request)
    {
        DB::enableQueryLog();
        $type = OperationType::findOrFail($request->validated('operation_type_id'));

        // if ($type->repayment)
        //     return response(trans('financial_operations.create.failure'), 500);
       // Log::debug($user);
        return $this->createOperationFromData($account, $request->validated());
    }

    public function createAdmin(User $user, Account $account, CreateOperationRequest $request)
    {
        Log::debug($user);
        if (is_null($user))
            $user = Auth::user();
        Log::debug(is_null($user));
        Log::debug($user);
        DB::enableQueryLog();
        $type = OperationType::findOrFail($request->validated('operation_type_id'));

        // if ($type->repayment)
        //     return response(trans('financial_operations.create.failure'), 500);

        return $this->createOperationFromDataAdmin($user,$account, $request->validated());
    }

    /**
     * Handles a request to create a new repayment operation.
     *
     * @param Lending $lending
     * the lending with which to associate the repayment
     * @param CreateRepaymentRequest $request
     * the request containing the repayment data
     * @return Application|ResponseFactory|Response
     * a response containing information about this operation's result
     */

    public function createRepayment(Lending $lending, CreateRepaymentRequest $request)
    {
        $lendingOperation = $lending->operation;

        if ($lendingOperation->isRepayment())
            return response(trans('financial_operations.create.failure'), 500);

        $account = $lendingOperation->account();
        $data = $request->prepareValidatedOperationData($lendingOperation);

        return $this->createOperationFromData($account, $data);
    }

    public function createRepaymentAdmin(User $user = null, Lending $lending, CreateRepaymentRequest $request)
    {

        $lendingOperation = $lending->operation;

        if ($lendingOperation->isRepayment())
            return response(trans('financial_operations.create.failure'), 500);

        $account = $lendingOperation->account();
        $data = $request->prepareValidatedOperationData($lendingOperation);

        return $this->createOperationFromDataAdmin($user,$account, $data);
    }


    /**
     * Creates a new financial operation from raw data.
     *
     * @param Account $account
     * the account with which to associate the operation
     * @param array $data
     * the data based on which to create the operation
     * (should contain values for all attributes in CreateOperationRequest and
     * optionally values for all attributes in CreateRepaymentRequest)
     * @return Application|ResponseFactory|Response
     * a response containing information about this operation's result
     */
    private function createOperationFromData(Account $account, array $data)
    {
        try {
            $attachment = $this->saveAttachment($account, $data);
            $this->createOperationWithinTransaction($account, $data, $attachment);
        } catch (Exception $e) {
            Log::debug('Creating financial operation failed, error: {e}', ['e' => $e]);
            if ($e instanceof ValidationException)
                throw $e;
            return response(trans('financial_operations.create.failure'), 500);
        }

        return response(trans('financial_operations.create.success'), 201);
    }


    private function createOperationFromDataAdmin(User $user = null, Account $account, array $data)
    {

        try {
            $attachment = $this->saveAttachment($account, $data);
            $this->createOperationWithinTransactionAdmin($user,$account, $data, $attachment);
        } catch (Exception $e) {
            Log::debug('Creating financial operation failed, error: {e}', ['e' => $e]);
            if ($e instanceof ValidationException)
                throw $e;
            return response(trans('financial_operations.create.failure'), 500);
        }

        return response(trans('financial_operations.create.success'), 201);
    }

    /**
     * Runs a database transaction in which a financial operation is created.
     *
     * @param Account $account
     * the account with which to associate the operation
     * @param array $data
     * the data based on which to create the operation
     * @param string|null $attachment
     * the path to the operation's attachment file (if any)
     * @return void
     * @throws Exception
     */
    private function createOperationWithinTransaction(Account $account, array $data, string|null $attachment)
    {
        $createRecordTransaction = new DBTransaction(
            fn() => $this->createOperationAndLendingRecord($account, $data, $attachment),
            fn() => FileHelper::deleteFileIfExists($attachment)
        );

        $createRecordTransaction->run();
    }

    private function createOperationWithinTransactionAdmin(User $user = null, Account $account, array $data, string|null $attachment)
    {

        $createRecordTransaction = new DBTransaction(
            fn() => $this->createOperationAndLendingRecordAdmin($user,$account, $data, $attachment),
            fn() => FileHelper::deleteFileIfExists($attachment)
        );

        $createRecordTransaction->run();
    }

    /**
     * Creates a record for the new operation, and, if needed, its associated
     * lending record.
     *
     * @param Account $account
     * the account with which to associate the operation
     * @param array $data
     * the data based on which to create the operation
     * @param string|null $attachment
     * the path to the operation's attachment file (if any)
     * @return void
     * @throws DatabaseException
     */

    private function createOperationAndLendingRecord(Account $account, array $data, string|null $attachment)
    {
        $operation = $this->createOperationRecord($account, $data, $attachment);
        Log::debug("Created an operation {e}", ['e' => $operation]);
        Log::debug("Is the operation a lending? {e}", ['e' => $operation->isLending()]);
        if ($operation->isLending())
            $this->upsertLending($operation, $data);
    }

    private function createOperationAndLendingRecordAdmin(User $user = null, Account $account, array $data, string|null $attachment)
    {

        $operation = $this->createOperationRecordAdmin($user, $account, $data, $attachment);
        Log::debug("Created an operation {e}", ['e' => $operation]);
        Log::debug("Is the operation a lending? {e}", ['e' => $operation->isLending()]);
        if ($operation->isLending())
            $this->upsertLending($operation, $data);
    }

    /**
     * Creates a new financial operation record in the database.
     *
     * This method is responsible for creating a record of a financial operation and associating it with a specific account.
     * It supports the addition of operations by administrators on accounts they do not own, ensuring that these operations
     * are correctly assigned to the account in question. For regular users, the operation is associated with their user account
     * as expected. For administrators performing operations on behalf of other accounts, the function ensures that the operation
     * is associated with the target account, not the administrator's personal account.
     *
     * @param Account $account The account with which to associate the operation. This account is the target of the financial operation.
     * @param array $data The data based on which to create the operation. This array should contain all necessary information required to create the operation record, excluding the expected date of return, which is removed at the beginning of the function.
     * @param string|null $attachment The path to the operation's attachment file, if any. This parameter allows for the association of an attachment with the financial operation, enhancing the operation's details and record-keeping capabilities.
     * @return FinancialOperation The model representing the created operation. This return value provides a direct reference to the newly created financial operation, allowing for further manipulation or inspection as required.
     * @throws DatabaseException Throws an exception if the operation cannot be created or if the user (in the case of regular users) does not have permission to create an operation on the account. For administrators, the function ensures that a valid user is associated with the account before proceeding.
     *
     */

    private function createOperationRecord(Account $account, array $data, string|null $attachment)
    {
        unset($data['expected_date_of_return']);
        unset($data['previous_lending_id']);
        DB::enableQueryLog();
        $currentUser = Auth::user();
        // Identifikujte, či operáciu vykonáva admin alebo bežný užívateľ
        $accountUser = $account->users()->where('users.id', $currentUser->id)->first();
        $accountUserId = $accountUser->pivot->id;
        $recordData = array_merge($data, ['attachment' => $attachment, 'account_user_id' => $accountUserId]);
        Log::debug('Creating financial operation data', ['data' => $recordData]);
        $operation = $account->operations()->updateOrCreate($recordData);

        if (!$operation->exists) {
            Log::error('The operation wasn\'t created.', ['data' => $recordData]);
            throw new DatabaseException('The operation wasn\'t created.');
        }

        return $operation;
    }

    private function createOperationRecordAdmin(User $user, Account $account, array $data, string|null $attachment)
    {
        unset($data['expected_date_of_return']);
        unset($data['previous_lending_id']);
        DB::enableQueryLog();

        Log::debug($user);
        // Identifikujte, či operáciu vykonáva admin alebo bežný užívateľ
        $accountUser = $account->users()->where('users.id', $user->id)->first();
        $accountUserId = $accountUser->pivot->id;
        $recordData = array_merge($data, ['attachment' => $attachment, 'account_user_id' => $accountUserId]);
        Log::debug('Creating financial operation data', ['data' => $recordData]);
        $operation = $account->operations()->updateOrCreate($recordData);

        if (!$operation->exists) {
            Log::error('The operation wasn\'t created.', ['data' => $recordData]);
            throw new DatabaseException('The operation wasn\'t created.');
        }

        return $operation;
    }


}


