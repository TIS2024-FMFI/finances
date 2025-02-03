<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Exceptions\DatabaseException;
use App\Http\Helpers\DBTransaction;
use App\Http\Helpers\FileHelper;
use App\Http\Requests\FinancialOperations\CreateOperationRequest;
use App\Http\Requests\FinancialOperations\CreateRepaymentRequest;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\FinancialOperation;
use App\Models\Lending;
use App\Models\OperationType;
use App\Models\SapOperation;
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

        return [
            'operation_types' => OperationType::userAssignable()->get(),
            'unrepaid_lendings' => Lending::where('isRepayed', 0)
                ->with(['operation_client_id', 'operation_host_id'])
                ->get(),
            'user_list' => $account->users()->get(),
            'operations' => $account->operations()->get(),
            'host_choice' => AccountUser::getAccountUserHosts(),
            'account' => $account,

        ];
    }


    public function getLendingData(Lending $lending)
    {
        return [
            'lending' => $lending
        ];
    }

    public function getOpposite(Lending $lending)
    {
        $lendObj = $lending;

        $client_operation = $lendObj->operation_client()->get()[0];
        $host_operation = $lendObj->operation_host()->get()[0];

        $client = $lendObj->client()->get()[0];
        $host = $lendObj->host()->get()[0];

        return [
            'lending' => $lending,
            "client_operation" => $client_operation,
            "host_operation" => $host_operation,
            'client' => $client,
            'host' => $host

        ];
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
	Log::debug("testingCreate");
	    
	    $user = Auth::user();
        $host = $request['host'];
	$repay_id = $request['repay_id'];

	Log::debug($request);

        return $this->createOperationFromData($user,$account, $request->validated(), $host, $repay_id);
    }

    public function createAdmin(User $user, Account $account, CreateOperationRequest $request)
    {
	Log::debug("testingCreateAdmin");

        if (is_null($user))
		$user = Auth::user();

	Log::debug($request);

        $host = $request['host'];
        $repay_id = $request['repay_id'];

        return $this->createOperationFromDataAdmin($user,$account, $request->validated(), $host, $repay_id);
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
    private function createOperationFromData(User $user = null, Account $account, array $data, $host, $repay_id)
    {
        try {
            $attachment = $this->saveAttachment($account, $data);
            $this->createOperationWithinTransaction($user,$account, $data, $attachment, $host, $repay_id);
        } catch (Exception $e) {
            Log::debug('Creating financial operation failed, error: {e}', ['e' => $e]);
            if ($e instanceof ValidationException)
                throw $e;
            return response(trans('financial_operations.create.failure'), 500);
        }

        return response(trans('financial_operations.create.success'), 201);
    }


    private function createOperationFromDataAdmin(User $user = null, Account $account, array $data, $host, $repay_id)
    {

        try {
            $attachment = $this->saveAttachment($account, $data);
            $this->createOperationWithinTransactionAdmin($user,$account, $data, $attachment, $host, $repay_id);
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
    private function createOperationWithinTransaction(User $user = null, Account $account, array $data, string|null $attachment, $host, $repay_id)
    {
        $createRecordTransaction = new DBTransaction(
            fn() => $this->createOperationAndLendingRecord($user,$account, $data, $attachment, $host, $repay_id),
            fn() => FileHelper::deleteFileIfExists($attachment)
        );

        $createRecordTransaction->run();
    }

    private function createOperationWithinTransactionAdmin(User $user = null, Account $account, array $data, string|null $attachment, $host, $repay_id)
    {

        $createRecordTransaction = new DBTransaction(
            fn() => $this->createOperationAndLendingRecordAdmin($user,$account, $data, $attachment, $host, $repay_id),
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

    private function createOperationAndLendingRecord(User $user = null, Account $account, array $data, string|null $attachment, $host, $repay_id)
    {
        $operation_client = $this->createOperationRecord($user, $account, $data, $attachment);

        if ($operation_client->isRepayment()){

            $accountUser = AccountUser::find($host);
            $hostUser = $accountUser->user;
            $hostAccount = $accountUser->account;
            $hostdata = array_merge([], $data);
            $hostdata['operation_type_id'] = "11";

            $operation_host = $this->createOperationRecord($hostUser, $hostAccount, $hostdata, $attachment);

            Log::debug("hakinoo");
            Log::debug($operation_client);
            Log::debug("$operation_host");

            Lending::findRepayment($repay_id)?->update(['isRepayed' => 1]);
        }

        else if ($operation_client->isLending()){

            $accountUser = AccountUser::find($host);
            $hostUser = $accountUser->user;
            $hostAccount = $accountUser->account;
            $hostdata = array_merge([], $data);
            $hostdata['operation_type_id'] = "10";

            $operation_host = $this->createOperationRecord($hostUser, $hostAccount, $hostdata, $attachment);

            Log::debug("JOKINOOOO");
            Log::debug($operation_client);
            Log::debug("$operation_host");

            $this->upsertLending($operation_client,$operation_host, $data, $host);
        }


    }

    private function createOperationAndLendingRecordAdmin(User $user = null, Account $account, array $data, string|null $attachment, $host, $repay_id)
    {

        $operation_client = $this->createOperationRecordAdmin($user, $account, $data, $attachment);

        if ($operation_client->isRepayment()){

            $accountUser = AccountUser::find($host);
            $hostUser = $accountUser->user;
            $hostAccount = $accountUser->account;
            $hostdata = array_merge([], $data);
            $hostdata['operation_type_id'] = "11";

            $operation_host = $this->createOperationRecordAdmin($hostUser, $hostAccount, $hostdata, $attachment);

            Log::debug("hakinoo");
            Log::debug($operation_client);
            Log::debug("$operation_host");

            Lending::findRepayment($repay_id)?->update(['isRepayed' => 1]);
        }

        else if ($operation_client->isLending()){

            $accountUser = AccountUser::find($host);
            $hostUser = $accountUser->user;
            $hostAccount = $accountUser->account;
            $hostdata = array_merge([], $data);
            $hostdata['operation_type_id'] = "10";

            $operation_host = $this->createOperationRecordAdmin($hostUser, $hostAccount, $hostdata, $attachment);

            Log::debug("JOKINOOOO");
            Log::debug($operation_client);
            Log::debug("$operation_host");

            $this->upsertLending($operation_client,$operation_host, $data, $host);
        }

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

    private function createOperationRecord(User $user, Account $account, array $data, string|null $attachment)
    {
        unset($data['expected_date_of_return']);
        unset($data['']);
        DB::enableQueryLog();

        Log::debug($user);
        // Identifikujte, či operáciu vykonáva admin alebo bežný užívateľ
        $accountUser = $account->users()->where('users.id', $user->id)->first();
        $accountUserId = $accountUser->pivot->id;
        $recordData = array_merge($data, ['attachment' => $attachment, 'account_user_id' => $accountUserId]);
        Log::debug('Creating financial operation data', ['data' => $recordData]);
        $operation = $account->operations()->create($recordData);

        if (!$operation->exists) {
            Log::error('The operation wasn\'t created.', ['data' => $recordData]);
            throw new DatabaseException('The operation wasn\'t created.');
        }

        return $operation;
    }

    private function createOperationRecordAdmin(User $user, Account $account, array $data, string|null $attachment)
    {
        unset($data['expected_date_of_return']);
        unset($data['']);
        DB::enableQueryLog();

        Log::debug($user);
        // Identifikujte, či operáciu vykonáva admin alebo bežný užívateľ
        $accountUser = $account->users()->where('users.id', $user->id)->first();
        $accountUserId = $accountUser->pivot->id;
        $recordData = array_merge($data, ['attachment' => $attachment, 'account_user_id' => $accountUserId, 'status' => 1]);
        Log::debug('Creating financial operation data', ['data' => $recordData]);
        $operation = $account->operations()->create($recordData);

        if (!$operation->exists) {
            Log::error('The operation wasn\'t created.', ['data' => $recordData]);
            throw new DatabaseException('The operation wasn\'t created.');
        }

        return $operation;
    }


}


