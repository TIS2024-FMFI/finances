<?php

namespace App\Http\Controllers\UserAccountManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAccountManagement\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
}
