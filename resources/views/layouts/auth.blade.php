@extends('layouts.blank', ['bodyClass' => 'account-page'])

@section('content')
    <div class="account-content">
        <div class="container">

            <!-- Account Logo -->
            <div class="account-logo">
                <a href="{{ url('/') }}">
                    @if(setting('whitelabel.login_logo'))
                        <img src="{{ Storage::url(setting('whitelabel.login_logo')) }}" alt="Logo">
                    @else
                        <img src="{{ asset('images/logo2.png') }}" alt="ERP Logo">
                    @endif
                </a>
            </div>
            <!-- /Account Logo -->

            @if(setting('whitelabel.login_background'))
            <style>
                body.account-page {
                    background-image: url('{{ Storage::url(setting('whitelabel.login_background')) }}') !important;
                    background-size: cover !important;
                    background-position: center !important;
                    background-repeat: no-repeat !important;
                }
            </style>
            @endif

            <div class="account-box">
                <div class="account-wrapper">
                    @yield('form')
                </div>
            </div>
        </div>
    </div>
@endsection
