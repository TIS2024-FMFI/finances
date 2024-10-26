<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\DatabaseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendLoginLinkRequest;
use App\Mail\LoginLink;
use App\Models\LoginToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use \App\Models\User;
use \Exception;

/**
 * A controller responsible for handling the situations when a user has forgotten
 * their password.
 * 
 * This controller provides methods to:
 *      - show the forgot-password form
 *      - send a login link to a user
 */
class ForgotPasswordController extends Controller
{
    /**
     * Show the Forgot Password view.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * the view that will be shown
     */
    public function show()
    {
        return view('auth.forgot_password');
    }

    /**
     * Handle a request to send a login link to a user's email address.
     * 
     * @param \App\Http\Requests\Auth\SendLoginLinkRequest $request
     * the request to handle
     * @return \Illuminate\Http\Response
     * a response containing the information about the result of this operation
     * presented as a plain-text message
     */
    public function sendLoginLink(SendLoginLinkRequest $request)
    {
        $email = $request->validated('email');
        
        try {
            $this->sendLoginLinkToEmailAddress($email);
        } catch (ModelNotFoundException|DatabaseException $e) {
            return response(trans('auth.login-link.generation.failed'), 500);
        } catch (Exception $e) {
            return response(trans('auth.login-link.sending.failed'), 500);
        }

        return response(trans('auth.login-link.sending.success'));
    }

    /**
     * Send a login link to a user's email address.
     * 
     * @param string $email
     * the email address of the user
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * thrown if no user was found for the given email address
     * @throws \App\Exceptions\DatabaseException
     * thrown if the login token generated for the user could not be persisted
     * @return void
     */
    private function sendLoginLinkToEmailAddress(string $email)
    {
        $user = User::where('email', $email)->firstOrFail();
        $token = $this->generateTokenForUser($user);
        
        $this->sendLoginLinkToUser($user, $token);
    }

    /**
     * Generate a new login link for a user.
     * 
     * @param \App\Models\User $user
     * the user for whom to generate the token
     * @throws \App\Exceptions\DatabaseException
     * thrown if the generated token could not be persisted
     * @return \App\Models\LoginToken
     * the generated token
     */
    private function generateTokenForUser(User $user)
    {
        $token = LoginToken::generate($user);

        if (!$token->exists) {
            throw new DatabaseException('Generated token not persisted.');
        }

        return $token;
    }

    /**
     * Send a login link to a user.
     * 
     * @param \App\Models\User $user
     * the user to whom to send the login link
     * @param \App\Models\LoginToken $token
     * the token to embed in the login link
     * @return void
     */
    private function sendLoginLinkToUser(User $user, LoginToken $token)
    {
        Mail::to($user->email)->send(
            new LoginLink($token->token, $token->valid_until)
        );
    }
}
