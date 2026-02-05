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
                        <li class="breadcrumb-item active">{{ __('Missed Punch') }}</li>
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
                            <i class="la la-edit text-primary" style="font-size: 1.5rem;"></i>
                            {{ __('Missed Punch Rules & Workflow') }}
                        </h5>
                        <p class="text-muted small mb-0 mt-2">
                            {{ __('Define how employees can request corrections for missed attendance records and who approves them.') }}
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.attendance-settings.missed-punch.update') }}" method="POST">
                            @csrf
                            
                            <!-- Retroactive Limit -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <label class="form-label fw-bold text-dark mb-1">{{ __('Retroactive Submission Limit') }} <span class="text-danger">*</span></label>
                                                <p class="text-muted small mb-0">{{ __('Maximum number of days in the past a request can be submitted.') }}</p>
                                            </div>
                                            <div class="col-auto">
                                                <div class="input-group" style="width: 150px;">
                                                    <input type="number" name="missed_punch_retroactive_limit" class="form-control @error('missed_punch_retroactive_limit') is-invalid @enderror" 
                                                           value="{{ old('missed_punch_retroactive_limit', $settings['missed_punch_retroactive_limit']) }}" min="0" max="30" required>
                                                    <span class="input-group-text bg-white border-start-0">{{ __('Days') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Limit -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <label class="form-label fw-bold text-dark mb-1">{{ __('Monthly Request Limit') }} <span class="text-danger">*</span></label>
                                                <p class="text-muted small mb-0">{{ __('Maximum number of missed punch requests an employee can make per month.') }}</p>
                                            </div>
                                            <div class="col-auto">
                                                <div class="input-group" style="width: 150px;">
                                                    <input type="number" name="missed_punch_max_requests_per_month" class="form-control @error('missed_punch_max_requests_per_month') is-invalid @enderror" 
                                                           value="{{ old('missed_punch_max_requests_per_month', $settings['missed_punch_max_requests_per_month']) }}" min="1" max="31" required>
                                                    <span class="input-group-text bg-white border-start-0">{{ __('Qty') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason Requirement -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="missed_punch_require_reason" name="missed_punch_require_reason" value="true" 
                                                   {{ $settings['missed_punch_require_reason'] ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-dark" for="missed_punch_require_reason">{{ __('Mandatory Reason Submission') }}</label>
                                            <p class="text-muted small mb-0">{{ __('If enabled, employees must provide a reason for every correction request.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Mode -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card border shadow-none">
                                        <div class="card-body">
                                            <h6 class="fw-bold d-flex align-items-center gap-2 mb-3">
                                                <i class="la la-user-check text-info"></i>
                                                {{ __('Approval Workflow Routing') }}
                                            </h6>
                                            <p class="text-muted small mb-3">
                                                {{ __('Select who is responsible for reviewing and approving these requests.') }}
                                            </p>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="form-check card-radio p-3 border rounded h-100 {{ $settings['missed_punch_approval_mode'] === 'manager' ? 'bg-primary-subtle border-primary' : '' }}">
                                                        <input class="form-check-input" type="radio" name="missed_punch_approval_mode" id="mode_manager" value="manager" 
                                                               {{ $settings['missed_punch_approval_mode'] === 'manager' ? 'checked' : '' }}>
                                                        <label class="form-check-label d-block" for="mode_manager">
                                                            <div class="fw-bold">{{ __('Direct Manager') }}</div>
                                                            <div class="small text-muted">{{ __('Direct supervisor reviews requests.') }}</div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check card-radio p-3 border rounded h-100 {{ $settings['missed_punch_approval_mode'] === 'hr' ? 'bg-primary-subtle border-primary' : '' }}">
                                                        <input class="form-check-input" type="radio" name="missed_punch_approval_mode" id="mode_hr" value="hr"
                                                               {{ $settings['missed_punch_approval_mode'] === 'hr' ? 'checked' : '' }}>
                                                        <label class="form-check-label d-block" for="mode_hr">
                                                            <div class="fw-bold">{{ __('HR Administrator') }}</div>
                                                            <div class="small text-muted">{{ __('HR department handles all requests.') }}</div>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check card-radio p-3 border rounded h-100 {{ $settings['missed_punch_approval_mode'] === 'multi' ? 'bg-primary-subtle border-primary' : '' }}">
                                                        <input class="form-check-input" type="radio" name="missed_punch_approval_mode" id="mode_multi" value="multi"
                                                               {{ $settings['missed_punch_approval_mode'] === 'multi' ? 'checked' : '' }}>
                                                        <label class="form-check-label d-block" for="mode_multi">
                                                            <div class="fw-bold">{{ __('Multi-Level') }}</div>
                                                            <div class="small text-muted">{{ __('Manager → HR Admin sequence.') }}</div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!$settings['missed_punch_approval_enabled'])
                                <div class="alert alert-warning d-flex align-items-start gap-3 border-0 shadow-sm mb-4">
                                    <i class="la la-exclamation-triangle mt-1" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <div class="fw-bold">{{ __('Missed Punch Feature is Disabled') }}</div>
                                        <div class="small opacity-75">
                                            {{ __('Employees currently cannot see or use this feature. Enable "Missed Punch Requests" in the main settings to activate.') }}
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

@push('page-styles')
<style>
    .card-radio:hover {
        border-color: #3b71ca !important;
        cursor: pointer;
    }
    .card-radio .form-check-input {
        display: none;
    }
    .bg-primary-subtle {
        background-color: rgba(59, 113, 202, 0.1) !important;
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.querySelectorAll('.card-radio').forEach(card => {
        card.addEventListener('click', () => {
            const radio = card.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Visual update
            document.querySelectorAll('.card-radio').forEach(c => {
                c.classList.remove('bg-primary-subtle', 'border-primary');
                c.querySelector('.small').classList.add('text-muted');
            });
            card.classList.add('bg-primary-subtle', 'border-primary');
            card.querySelector('.small').classList.remove('text-muted');
        });
    });
</script>
@endpush
