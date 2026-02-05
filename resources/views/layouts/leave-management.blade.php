@extends('layouts.app')

@push('page-styles')
<link rel="stylesheet" href="{{ asset('css/leave-management.css') }}">
@endpush

@section('page-content')
<div class="content container-fluid">
    {{-- Top Tab Navigation --}}
    <div class="leave-nav-container">
        <div class="leave-nav-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave.my-time*') ? 'active' : '' }}" 
                       href="{{ route('leave.my-time') }}">
                        <i class="fa fa-user-clock"></i>
                        <span>{{ __('My Time') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave.overview*') ? 'active' : '' }}" 
                       href="{{ route('leave.overview') }}">
                        <i class="fa fa-users"></i>
                        <span>{{ __('Overview') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave.management*') ? 'active' : '' }}" 
                       href="{{ route('leave.management') }}">
                        <i class="fa fa-tasks"></i>
                        <span>{{ __('Management') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave.reporting*') ? 'active' : '' }}" 
                       href="{{ route('leave.reporting') }}">
                        <i class="fa fa-chart-bar"></i>
                        <span>{{ __('Reporting') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('leave.configuration*') ? 'active' : '' }}" 
                       href="{{ route('leave.configuration') }}">
                        <i class="fa fa-cog"></i>
                        <span>{{ __('Configuration') }}</span>
                    </a>
                </li>
            </ul>

            {{-- Configuration Gear Icon (Quick Access) --}}
            <div class="config-quick-access dropdown">
                <button class="btn btn-icon" id="configDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-cog fa-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="configDropdown">
                    <li><h6 class="dropdown-header">{{ __('Configuration') }}</h6></li>
                    <li><a class="dropdown-item" href="{{ route('leave.config.time-off-types') }}">
                        <i class="fa fa-list"></i> {{ __('Time Off Types') }}
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('leave.config.accrual-plans') }}">
                        <i class="fa fa-calendar-plus"></i> {{ __('Accrual Plans') }}
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('leave.config.public-holidays.index') }}">
                        <i class="fa fa-calendar-day"></i> {{ __('Public Holidays') }}
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('leave.config.mandatory-days') }}">
                        <i class="fa fa-exclamation-circle"></i> {{ __('Mandatory Days') }}
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('leave.configuration') }}">
                        <i class="fa fa-sliders-h"></i> {{ __('General Settings') }}
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="leave-content-area">
        @yield('leave-content')
    </div>
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('js/leave-management.js') }}"></script>
@endpush
