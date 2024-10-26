{!!
    nl2br(
        trans(
            'emails.login-link.content',
            [ 
                'appName' => config('app.name'),
                'validUntil' => $validUntil,
                'url' => $url,
            ]
        )
    )
!!}