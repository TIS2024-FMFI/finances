<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href={{ asset('images/credit-card-fill.svg') }}>
    <link href={{ asset('css/main.css') }} rel="stylesheet">
    <link href={{ asset('css/modals.css') }} rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('common.app_root_script')
    <script src={{ asset('js/main.js') }} rel="stylesheet"></script>
    <title>Financie</title>

</head>
<body data-is-admin="{{ Auth::user()->user_type == 2 ? 'true' : 'false' }}">

<nav>
    <div class="navbar">
        <div class="top-bar">
            <div class="content-inner">
                <span id="top-bar-email">
                    {{ Auth::User()->email }}
                </span>
                <div class="dropdown">
                    <button class="dropbtn"><i class="bi bi-caret-down-fill fs-8"></i></button>
                    <div class="dropdown-content">
                       <a href="https://pritomnost.dai.fmph.uniba.sk/" target="_blank">Vytvor nový ŠPP</a>
                    </div>
                </div>
                <form method="POST" action={{ route('logout') }}>
                    @csrf
                    <button type="submit" id="logout-button"><i class="bi bi-box-arrow-right fs-8"></i></button>
                </form>
            </div>
        </div>
        <a @if(auth()->user()->user_type == 2)
                        href={{ route('admin_home') }}
                        @else
                        href={{ route('home') }}
                        @endif>
            <div class="content-inner">
                <div class="logo">
                    <h1>FINANCIE</h1>
                    <p>KATEDRA APLIKOVANEJ INFORMATIKY</p>
                </div>
            </div>
        </a>
    </div>
</nav>
<div class="content">
    <div class="content-inner">
        <div class="main-wrapper">






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
