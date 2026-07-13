@extends('layouts.blank', ['bodyClass' => 'account-page'])

@section('content')
    <div class="account-content">
        <div class="container">



            @php $loginBackground = brandingAsset('login_background'); @endphp
            @if($loginBackground)
            <style>
                body.account-page {
                    background-image: url('{{ $loginBackground }}') !important;
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
