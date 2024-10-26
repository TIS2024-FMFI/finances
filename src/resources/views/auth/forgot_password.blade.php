<!DOCTYPE html>
<html lang="sk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="icon" type="image/x-icon" href={{ asset('images/credit-card-fill.svg') }}>
        <link href={{ asset('css/main.css') }} rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @include('common.app_root_script')
        <script src={{ asset('js/main.js') }} rel="stylesheet"></script>
        <title>Prihlásenie</title>
    </head>
    <body>
        <div class="login-box">
            <div class="login">
                
                <h1>Zabudnuté heslo</h1>

                <form id="forgot-pass-form">
                    <div class="input-box">
                        <div class="field">
                            <input type="text" id="forgot-pass-email">
                            <label for="forgot-pass-email">E-mailová adresa</label>
                        </div>
                        <div class="error-box" id="forgot-pass-email-errors"></div>
                    </div>

                    <button type="submit" data-csrf="{{ csrf_token() }}" id="forgot-pass-button">Odoslať</button>
                </form>
            </div>
        </div>
    </body>
</html>