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
    <body class="login-box">
        <div class="modal-box">

            <div class="modal">

                <h2>Nový používateľ</h2>

                <form id="first-user-form">
                    
                    <div class="input-box">
                        <div class="field">
                        <input type="email" id="first-user-email">
                            <label for="first-user-email">E-mailová adresa</label>
                        </div>
                        <div class="error-box" id="first-user-email-errors"></div>
                    </div>
                    
                    <button type="submit" data-csrf="{{ csrf_token() }}" id="first-user-button">Vytvoriť</button>
                </form>
            </div>

        </div>
    </body>
</html>