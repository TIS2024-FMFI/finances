<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to construct emails.
    |
    */

    'login-link' => [
        'subject' => 'Login Link for :appName',
        'content' => <<<HERE
            Hi,
            
            you have asked us to send you a login link to be able to log into your account at :appName.
            The link we generated for you can be found below, but note that it is valid only until :validUntil.
            
            :url
            
            Best regards,
            Your :appName team
            HERE,
    ],

];
