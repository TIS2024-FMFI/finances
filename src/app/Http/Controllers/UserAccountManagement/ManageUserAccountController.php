<?php

namespace App\Http\Controllers\UserAccountManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAccountManagement\ChangePasswordRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AccountUser;

/**
 * A controller responsible for user account management.
 * 
 * This controller provides methods to:
 *      - change a user's password
 */
class ManageUserAccountController extends Controller
{
    /**
     * Handles a request to change the password of the currently authenticated user.
     * 
     * @param \App\Http\Requests\UserAccountManagement\ChangePasswordRequest $request
     * the request to handle
     * @return \Illuminate\Http\Response
     * a response containing the information about the result of this operation
     * presented as a plain-text message
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $password = $request->validated('new_password');
        $user = Auth::user();
        
        if ($user->setPassword($password)) {
            Auth::logoutOtherDevices($password);

            return response(trans('passwords.change.success'));
        }

        return response(trans('passwords.change.failed'), 500);
    }

    public function addUserToAccount(Request $request, Account $account)
    {
        Log::debug('Adding user to account:', ['account_id' => $account->id, 'user_id' => $request->user_id]);

        if (!User::where('id', $request->user_id)->exists()) {
            return response()->json(['message' => 'Používateľ neexistuje.'], 400);
        }

        $exists = AccountUser::where('account_id', $account->id)
                            ->where('user_id', $request->user_id)
                            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Používateľ je už pridaný k účtu.'], 400);
        }

        AccountUser::create([
            'account_id' => $account->id,
            'user_id' => $request->user_id,
            // Temporary fix for missing title
            'account_title' => 'title'
        ]);

        return response()->json(['message' => 'Používateľ bol úspešne pridaný.'], 201);
    }


    public function getFormData(Account $account)
    {
        Log::debug('Fetching users who are not already in the account', ['account_id' => $account->id]);

        $existingUserIds = AccountUser::where('account_id', $account->id)
            ->pluck('user_id')
            ->toArray();

	// original mystery:
        // $users = User::whereNotIn('id', $existingUserIds)
        //    ->whereHas('accounts', function ($query) use ($account) {
        //        $query->where('account_id', '!=', $account->id);
        //    })
        //    ->distinct()
        //    ->get();

	// our try
        $users = User::whereNotIn('id', $existingUserIds)->get();

        Log::debug('Users available to be added:', ['users' => $users]);

        return response()->json(['users' => $users]);
    }


    public function removeUserFromAccount($accountId, $userId)
    {
        Log::debug('Removing user from account', ['account_id' => $accountId, 'user_id' => $userId]);

        $accountId = intval($accountId);
        $userId = intval($userId);

        $accountUser = AccountUser::where('account_id', $accountId)
        ->where('user_id', $userId)
        ->first();

        if (!$accountUser) {
            return response()->json(['message' => 'User is not associated with this account.'], 404);
        }

        $accountUser->delete();

        return response()->json(['message' => 'User successfully removed from the account.'], 200);
    }
}
