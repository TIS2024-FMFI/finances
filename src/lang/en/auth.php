<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'success' => 'Welcome, you successfully logged in.',
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login-link' => [
        'generation' => [
            'failed' => 'We were unable to generate a login token, please, try again later.',
        ],
        'sending' => [
            'success' => 'The login link should be delivered to your email address in a few moments.',
            'failed' => 'We were unable to email you a login link, please, try again later.',
        ]
    ],
    'register' => [
        'success' => 'Successfully registered a new user.',
        'failed' => 'We were unable to register a new user, please, try again later.',
    ],

];
