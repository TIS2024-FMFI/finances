<?php

namespace App\Http\Controllers\FinancialAccounts;

use App\Exceptions\DatabaseException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FinancialAccounts\CreateAccountController;
use App\Http\Requests\FinancialAccounts\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

/**
 * A controller responsible for updating financial accounts.
 *
 * This controller provides methods to:
 *      - update a financial account
 */
class UpdateAccountController extends Controller
{
    /**
     * Handle a request to update a financial account.
     *
     * @param \App\Http\Requests\FinancialAccounts\UpdateAccountRequest $request
     * the request containing the updated version of account data
     * @param \App\Models\Account $account
     * the account update
     * @return \Illuminate\Http\Response
     * a response containing the information about the result of this operation
     * presented as a plain-text message
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $data = $this->extractAccountData($request);

        try {
            $this->upsertAccountRecord($account, $data);
        } catch (DatabaseException $e) {
            return response(trans('financial_accounts.update.failed'), 500);
        }

        return response(trans('financial_accounts.update.success'));
    }

    /**
     * Extract account data from a request.
     *
     * @param \App\Http\Requests\FinancialAccounts\UpdateAccountRequest $request
     * the request from which to extract data
     * @return array
     * the extracted data
     */
    private function extractAccountData(UpdateAccountRequest $request)
    {
        return [
            'title' => $request->validated('title'),
            'sap_id' => $request->validated('sap_id')
        ];
    }

    /**
     * Update a financial account record.
     * If SAP ID is changed either existing account 
     * is attached to the user or a new account is made.
     *
     * @param \App\Models\Account $account
     * the record to update
     * @param array $data
     * the updated version of account data (except for user_id)
     * @return void
     */
    private function upsertAccountRecord(Account $account, array $data)
    {
        if ($account->sap_id === $data['sap_id'])
        {
            $account->users()->updateExistingPivot(Auth::user()->id, ['account_title' => $data['title']]);
        }
        else
        {
            $account->users()->detach(Auth::user());
            $this->createOrUpdateAccountUserRecord($data);
        }
    }

    /**
     * Finds or creates a new Account and attaches
     * the authentificated User to it
     *
     * @param array $data
     * the updated version of account data (except for user_id)
     * @return void
     */

    private function createOrUpdateAccountUserRecord($data){
        $user = Auth::user();

        $newAccount = Account::firstOrCreate([
            'sap_id' => $data['sap_id'],
        ]);
        if (! $user->accounts->contains($newAccount))
            $user->accounts()->attach($newAccount, ['account_title' => $data['title']]);
    }
}
