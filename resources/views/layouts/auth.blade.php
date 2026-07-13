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
                    @php
                        $brandName = brand('name');
                        $loginLogo = brand('login_logo');
                    @endphp
                    <div class="text-center mb-4">
                        <a href="{{ route('login') }}" class="d-inline-flex align-items-center justify-content-center" style="text-decoration: none;">
                            @if($loginLogo)
                                <img src="{{ $loginLogo }}" alt="{{ $brandName }}" style="max-height: 48px; max-width: 220px; object-fit: contain;">
                            @else
                                <span class="fw-bold fs-4 text-dark">{{ $brandName }}</span>
                            @endif
                        </a>
                    </div>
                    @yield('form')
                    <div class="text-center mt-4 small text-muted">
                        {{ __('Powered by') }} {{ $brandName }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
