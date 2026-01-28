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
                        <li class="breadcrumb-item active">{{ __('Night Shift') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="la la-moon text-primary" style="font-size: 1.5rem;"></i>
                            {{ __('Night Time Range') }}
                        </h5>
                        <p class="text-muted small mb-0 mt-2">
                            {{ __('Define the window that is considered as night time. These settings are used when Night Shift support is disabled to prevent scheduling shifts during these hours.') }}
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.attendance-settings.night-shift.update') }}" method="POST">
                            @csrf
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('Night Time Start') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="la la-clock"></i></span>
                                        <input type="time" name="night_time_start" class="form-control border-start-0 ps-0 @error('night_time_start') is-invalid @enderror" 
                                               value="{{ old('night_time_start', $settings['night_time_start']) }}" required>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        {{ __('e.g., 22:00 (10 PM)') }}
                                    </div>
                                    @error('night_time_start')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark">{{ __('Night Time End') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="la la-clock"></i></span>
                                        <input type="time" name="night_time_end" class="form-control border-start-0 ps-0 @error('night_time_end') is-invalid @enderror" 
                                               value="{{ old('night_time_end', $settings['night_time_end']) }}" required>
                                    </div>
                                    <div class="small text-muted mt-2">
                                        {{ __('e.g., 06:00 (6 AM)') }}
                                    </div>
                                    @error('night_time_end')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(!$settings['night_shift_enabled'])
                                <div class="alert alert-warning d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                                    <i class="la la-exclamation-triangle mt-1" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <div class="fw-bold">{{ __('Night Shift Support is Currently Disabled') }}</div>
                                        <div class="small opacity-75">
                                            {{ __('Since night shifts are disabled, the system will block any new shifts that overlap with the range defined above.') }}
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
