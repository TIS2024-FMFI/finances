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

                <h1>Prihlásenie</h1>

                <form method="POST" action="" id="login-form">
                    @csrf
                    
                    <div class="input-box">
                        @if ($errors->has('email'))
                            <div class="field">
                                <input type="text" name="email" id="login-email" value="{{ old('email') }}" style="border-color: red;">
                                <label for="login-email">E-mailová adresa</label>
                            </div>
                            <div class="error-box">
                                <p>{{ $errors->first('email') }}</p>
                            </div>
                        @else
                            <div class="field">
                                <input type="text" name="email" id="login-email" value="{{ old('email') }}">
                                <label for="login-email">E-mailová adresa</label>
                            </div>
                        @endif
                    </div>
                    
                    <div class="input-box">
                        @if ($errors->has('password'))
                            <div class="field" style="position: relative;">
                                <input type="password" name="password" style="border-color: red;" id="login-pass">
                                <i class="bi bi-eye show-pass" id="show-login-pass"></i>
                                <label for="login-pass">Heslo</label>
                            </div>
                            <div class="error-box">
                                <p>{{ $errors->first('password') }}</p>
                            </div>
                        @else 
                            <div class="field" style="position: relative;">
                                <input type="password" name="password" id="login-pass">
                                <i class="bi bi-eye show-pass" id="show-login-pass"></i>
                                <label for="login-pass">Heslo</label>
                            </div>
                        @endif
                    </div>

                    <button type="submit">Prihlásiť sa</button>
                    <a href={{ route('forgot-password') }}>Zabudli ste heslo?</a>

                </form>
            </div>
        </div>
    </body>
</html>