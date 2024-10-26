<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;

/**
 * A controller responsible for logging users in and out of the application.
 *
 * This controller provides methods to:
 *      - show the login form
 *      - log in a user via email an password
 *      - log in a user via login link
 *      - log out a user
 */
class LoginController extends Controller
{
    /**
     * Show the Login view if there is already a registered user.
     * Otherwise, show the First User view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * the view that will be shown
     */
    public function show()
    {
        if (!User::first()) {
            return view('auth.first_user');
        }

        return view('auth.login');
    }

    /**
     * Handle a request to log in via email and password.
     *
     * @param \App\Http\Requests\Auth\LoginRequest  $request
     * the request to handle
     * @return \Illuminate\Http\RedirectResponse
     * a response redirecting the user to the intended location (by default "home")
     * if authenticated; otherwise, a response redirecting the user back to the login form
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (Auth::user()->is_admin) {
                return redirect(route('admin_home'));
            } else {
                return redirect(route('home'));
            }
        }

        return back()
                ->withErrors([ 'email' => trans('auth.failed') ])
                ->onlyInput('email');
    }

    /**
     * Handle a request to log in via a login token.
     *
     * @param \Illuminate\Http\Request $request
     * the request to handle
     * @param string $token
     * the login token to use for authentication
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * a response redirecting the user to the "home" location if authenticated;
     * otherwise, a view informing the user that the provided token is invalid
     */
    public function loginUsingToken(Request $request, string $token)
    {
        $token = LoginToken::where('token', $token)->first();

        if ($token && $token->isValid()) {
            Auth::login($token->user);
            $token->invalidate();

            return redirect(route('home'));
        }

        return view('auth.login_link_invalid');
    }

    /**
     * Handle a request to log the currently authenticated user out of
     * the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * the request to handle
     * @return \Illuminate\Http\RedirectResponse
     * a response redirecting the user to the login form
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('login', [], false));
    }
}
