<?php

namespace App\Http\Controllers\FinancialAccounts;

use App\Http\Requests\FinancialAccounts\CreateAccountRequest;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

/**
 * Manages creation of new financial accounts.
 */
class CreateAccountController
{

    /**
     * Handles the request to add a new financial account for the current user
     *
     * @param CreateAccountRequest $request
     * the HTTP request to create an operation
     * @return Application|ResponseFactory|Response
     * a response containing information about this operation's result
     */
    public function create(CreateAccountRequest $request)
    {
        $user = Auth::user();
        Log::debug($user);
        $account = Account::firstOrCreate([
            'sap_id' => $request->validated('sap_id'),
        ]);

        if (! $account->exists)
            return response(trans('financial_accounts.create.failed'), 500);

        if (! $user->accounts->contains($account))
            $user->accounts()->attach($account, ['account_title' => $request->validated('title')]);
        $admins = User::where('is_admin',1);
        foreach ($admins as $admin){
            if (! $admin->accounts->contains($account))
                $admin->accounts()->attach($account, ['account_title' => $request->validated('title')]);

        }
        return response(trans('financial_accounts.create.success'), 201);
    }

    public function createAdmin(User $user, CreateAccountRequest $request)
    {
        Log::debug($user);
        if (is_null($user))
            $user = Auth::user();
        Log::debug($user);
        $account = Account::firstOrCreate([
            'sap_id' => $request->validated('sap_id'),
        ]);

        if (! $account->exists)
            return response(trans('financial_accounts.create.failed'), 500);

        if (! $user->accounts->contains($account))
            $user->accounts()->attach($account, ['account_title' => $request->validated('title')]);
        $admins = User::where('is_admin',1);
        foreach ($admins as $admin){
            if (! $admin->accounts->contains($account))
                $admin->accounts()->attach($account, ['account_title' => $request->validated('title')]);

        }
        return response(trans('financial_accounts.create.success'), 201);
    }

}
