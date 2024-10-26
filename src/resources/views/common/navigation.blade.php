<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href={{ asset('images/credit-card-fill.svg') }}>
    <link href={{ asset('css/main.css') }} rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('common.app_root_script')
    <script src={{ asset('js/main.js') }} rel="stylesheet"></script>
    <title>BudgetMaster</title>
</head>
<body data-is-admin="{{ Auth::user()->is_admin ? 'true' : 'false' }}">
<nav>
    <div>
        <a @if(auth()->user()->is_admin)
               href={{ route('admin_home') }}
                    @else
                       href={{ route('home') }}
            @endif><i class="bi bi-credit-card-fill"></i> BudgetMaster</a>
    </div>
    <div class="dropdown">
        <button class="dropbtn">{{ Auth::User()->email }}<i class="bi bi-caret-down-fill"></i></button>
        <div class="dropdown-content">
            <a class="change-pass">Zmeniť heslo</a>
            <a class="create-user">Vytvoriť používateľa</a>
            <form method="POST" action={{ route('logout') }}>
                @csrf
                <button type="submit" id="logout-button">Odhlásiť sa</button>
            </form>
        </div>
    </div>
</nav>
<div class="content">

@include('auth.modals.create_user')

@if(isset($open_change_password))
    @include('user_account_management.modals.change_password', ['open' => $open_change_password])
@else
    @include('user_account_management.modals.change_password')
@endif

@include('finances.modals.operation')
@include('finances.modals.create_operation')
@include('finances.modals.edit_operation')
@include('finances.modals.check_operation')
@include('finances.modals.uncheck_operation')
@include('finances.modals.delete_operation')
@include('finances.modals.repayment')

@include('finances.modals.add_report')

@include('finances.modals.create_account')
@include('finances.modals.edit_account')
@include('finances.modals.delete_account')
@include('finances.modals.delete_report')

@include('finances.modals.loader')

@include('finances.modals.check_sap_operation')
@include('finances.modals.uncheck_sap_operation')
