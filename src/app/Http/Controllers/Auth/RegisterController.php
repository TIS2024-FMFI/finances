<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DatabaseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use \Exception;

/**
 * A controller responsible for registering new users.
 * 
 * This controller provides methods to:
 *      - register a new user
 */
class RegisterController extends Controller
{
    /**
     * Handle a request to register a new user.
     * 
     * @param \App\Http\Requests\Auth\RegisterRequest $request
     * the request to handle
     * @return \Illuminate\Http\Response
     * a response containing the information about the result of this operation
     * presented as a plain-text message
     */
    public function register(RegisterRequest $request)
    {
        $email = $request->validated('email');

        try {
            $this->registerWithEmail($email);
        } catch (Exception $e) {
            return response(trans('auth.register.failed'), 500);
        }

        return response(trans('auth.register.success'), 201);
    }

    /**
     * Register a new user identified by an email address.
     * 
     * @param string $email
     * the email address identifying the new user
     * @throws \App\Exceptions\DatabaseException
     * thrown if an unspecified database error ocurred during the creation process
     * @return void
     */
    private function registerWithEmail(string $email)
    {
        $user = User::create([ 'email' => $email ]);

        if (!$user->exists) {
            throw new DatabaseException('User model not saved.');
        }
    }
}
