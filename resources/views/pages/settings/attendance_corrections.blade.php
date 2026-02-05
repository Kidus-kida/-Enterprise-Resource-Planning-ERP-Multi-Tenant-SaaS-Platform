@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ $pageTitle }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendance-settings.index') }}">{{ __('Attendance Settings') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Corrections') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <form action="{{ route('admin.attendance-settings.corrections.update') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                                    <i class="la la-edit text-primary" style="font-size: 1.5rem;"></i>
                                    {{ __('Attendance Correction Policies') }}
                                </h5>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.attendance-settings.index') }}" class="btn btn-outline-secondary btn-sm px-4">
                                        <i class="la la-arrow-left"></i> {{ __('Back to Settings') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-sm px-4">
                                        <i class="la la-save"></i> {{ __('Save Configuration') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ __('Configure how admins and managers can directly modify attendance records.') }}
                            </p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <!-- Retroactive Limit -->
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">{{ __('Retroactive Limit (Days)') }}</label>
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="number" name="correction_retroactive_limit" class="form-control" 
                                                   value="{{ old('correction_retroactive_limit', $settings['correction_retroactive_limit']) }}" 
                                                   min="0" max="365" required>
                                            <span class="input-group-text">{{ __('Days') }}</span>
                                        </div>
                                        <div class="form-text text-muted">
                                            {{ __('How many days back can an admin or manager modify attendance? (0 = No limit)') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Require Reason -->
                                <div class="col-12">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="correction_require_reason" 
                                               name="correction_require_reason" value="true" 
                                               {{ $settings['correction_require_reason'] ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="correction_require_reason">
                                            {{ __('Require Reason for Correction') }}
                                        </label>
                                    </div>
                                    <div class="form-text text-muted ms-4">
                                        {{ __('Force the admin to provide an explanation for every manual change.') }}
                                    </div>
                                </div>

                                <!-- Audit Trail -->
                                <div class="col-12">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="correction_audit_trail_enabled" 
                                               name="correction_audit_trail_enabled" value="true" 
                                               {{ $settings['correction_audit_trail_enabled'] ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="correction_audit_trail_enabled">
                                            {{ __('Enable Advanced Audit Trail') }}
                                        </label>
                                    </div>
                                    <div class="form-text text-muted ms-4">
                                        {{ __('Keep a detailed log of original vs corrected times and who made the change.') }}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.attendance-settings.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="la la-arrow-left"></i> {{ __('Back to Settings') }}
                                </a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="la la-save me-1"></i> {{ __('Save Configuration') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
