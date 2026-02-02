@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.attendance-settings.index') }}">{{ __('Attendance Settings') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Flexible Hours') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="la la-clock text-primary" style="font-size: 1.5rem;"></i>
                            {{ __('Flexible Schedule Configuration') }}
                        </h5>
                        <p class="text-muted small mb-0 mt-2">
                            {{ __('Define the parameters for flexible work timing. Flexible hours focus on total work duration rather than strict start/end times.') }}
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.attendance-settings.flexible.update') }}" method="POST">
                            @csrf
                            
                            <!-- Target Hours -->
                            <div class="row mb-5">
                                <div class="col-md-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <label class="form-label fw-bold text-dark mb-1">{{ __('Daily Target Work Hours') }} <span class="text-danger">*</span></label>
                                                <p class="text-muted small mb-0">{{ __('The total duration an employee is expected to work per day.') }}</p>
                                            </div>
                                            <div class="col-auto">
                                                <div class="input-group" style="width: 150px;">
                                                    <input type="number" step="0.5" name="flexible_daily_target_hours" class="form-control @error('flexible_daily_target_hours') is-invalid @enderror" 
                                                           value="{{ old('flexible_daily_target_hours', $settings['flexible_daily_target_hours']) }}" required>
                                                    <span class="input-group-text bg-white border-start-0">{{ __('Hrs') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @error('flexible_daily_target_hours')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <!-- Core Time Range -->
                                <div class="col-md-6">
                                    <div class="card border shadow-none h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold d-flex align-items-center gap-2 mb-3">
                                                <i class="la la-users text-info"></i>
                                                {{ __('Core Attendance Window') }}
                                            </h6>
                                            <p class="text-muted small mb-3">
                                                {{ __('The period during which employee presence is mandatory (e.g., for meetings).') }}
                                            </p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">{{ __('Core Time Start') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="la la-hourglass-start"></i></span>
                                                    <input type="time" name="flexible_core_start_time" class="form-control @error('flexible_core_start_time') is-invalid @enderror" 
                                                           value="{{ old('flexible_core_start_time', $settings['flexible_core_start_time']) }}" required>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="form-label small fw-bold">{{ __('Core Time End') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="la la-hourglass-end"></i></span>
                                                    <input type="time" name="flexible_core_end_time" class="form-control @error('flexible_core_end_time') is-invalid @enderror" 
                                                           value="{{ old('flexible_core_end_time', $settings['flexible_core_end_time']) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Flex Window -->
                                <div class="col-md-6">
                                    <div class="card border shadow-none h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold d-flex align-items-center gap-2 mb-3">
                                                <i class="la la-door-open text-success"></i>
                                                {{ __('Flexibility Window') }}
                                            </h6>
                                            <p class="text-muted small mb-3">
                                                {{ __('The absolute earliest and latest times an employee is allowed to punch.') }}
                                            </p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">{{ __('Earliest Clock-In') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="la la-sun"></i></span>
                                                    <input type="time" name="flexible_window_start" class="form-control @error('flexible_window_start') is-invalid @enderror" 
                                                           value="{{ old('flexible_window_start', $settings['flexible_window_start']) }}" required>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="form-label small fw-bold">{{ __('Latest Clock-Out') }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="la la-moon"></i></span>
                                                    <input type="time" name="flexible_window_end" class="form-control @error('flexible_window_end') is-invalid @enderror" 
                                                           value="{{ old('flexible_window_end', $settings['flexible_window_end']) }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!$settings['flexible_hours_enabled'])
                                <div class="alert alert-info d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                                    <i class="la la-info-circle mt-1" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <div class="fw-bold">{{ __('Flexible Hours Feature is Currently Disabled') }}</div>
                                        <div class="small opacity-75">
                                            {{ __('You can configure these settings now, but they will only apply once you enable "Flexible Hours" on the main Attendance Settings page.') }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between mt-5 pt-3 border-top">
                                <a href="{{ route('admin.attendance-settings.index') }}" class="btn btn-light px-4">
                                    <i class="la la-arrow-left me-1"></i> {{ __('Back to Settings') }}
                                </a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="la la-save me-1"></i> {{ __('Save Configuration') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
