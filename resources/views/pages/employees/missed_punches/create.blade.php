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
                        <li class="breadcrumb-item"><a href="{{ route('missed-punches.index') }}">{{ __('Missed Punches') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Submit Request') }}</li>
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
                            <i class="la la-calendar-plus text-primary" style="font-size: 1.5rem;"></i>
                            {{ __('Attendance Correction Request') }}
                        </h5>
                        <p class="text-muted small mb-0 mt-2">
                            {{ __('Use this form to request a correction if you forgot to clock in or out. Your request will be sent to your manager for approval.') }}
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('missed-punches.store') }}" method="POST">
                            @csrf
                            
                            @if($errors->has('error'))
                                <div class="alert alert-danger mb-4">
                                    {{ $errors->first('error') }}
                                </div>
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('Date of Missed Punch') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="la la-calendar"></i></span>
                                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                                               value="{{ old('date', $date) }}" max="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ __('Note: You can submit requests for the past :days days.', ['days' => $retroactiveLimit]) }}
                                    </div>
                                    @error('date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('What did you miss?') }} <span class="text-danger">*</span></label>
                                    <select name="punch_type" id="punch_type" class="form-select @error('punch_type') is-invalid @enderror" onchange="toggleTimeFields()" required>
                                        <option value="clock_in" {{ old('punch_type') === 'clock_in' ? 'selected' : '' }}>{{ __('Missed Clock-In') }}</option>
                                        <option value="clock_out" {{ old('punch_type') === 'clock_out' ? 'selected' : '' }}>{{ __('Missed Clock-Out') }}</option>
                                        <option value="both" {{ old('punch_type') === 'both' ? 'selected' : '' }}>{{ __('Missed Both (In & Out)') }}</option>
                                    </select>
                                    @error('punch_type')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6" id="start_time_container">
                                    <label class="form-label fw-bold">{{ __('Requested Clock-In Time') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="la la-clock"></i></span>
                                        <input type="time" name="requested_start_time" class="form-control @error('requested_start_time') is-invalid @enderror" 
                                               value="{{ old('requested_start_time') }}">
                                    </div>
                                    @error('requested_start_time')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6" id="end_time_container" style="display: none;">
                                    <label class="form-label fw-bold">{{ __('Requested Clock-Out Time') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="la la-clock"></i></span>
                                        <input type="time" name="requested_end_time" class="form-control @error('requested_end_time') is-invalid @enderror" 
                                               value="{{ old('requested_end_time') }}">
                                    </div>
                                    @error('requested_end_time')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __('Reason for Correction') }} @if($requireReason) <span class="text-danger">*</span> @endif</label>
                                <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" 
                                          placeholder="{{ __('e.g. Forgot to clock in due to emergency meeting...') }}" {{ $requireReason ? 'required' : '' }}>{{ old('reason') }}</textarea>
                                <div class="small text-muted mt-1">{{ __('Please provide a detailed explanation (min 10 characters).') }}</div>
                                @error('reason')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-5 pt-3 border-top">
                                <a href="{{ route('missed-punches.index') }}" class="btn btn-light px-4">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="la la-paper-plane me-1"></i> {{ __('Submit Request') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-scripts')
<script>
    function toggleTimeFields() {
        const type = document.getElementById('punch_type').value;
        const startContainer = document.getElementById('start_time_container');
        const endContainer = document.getElementById('end_time_container');
        
        if (type === 'clock_in') {
            startContainer.style.display = 'block';
            endContainer.style.display = 'none';
        } else if (type === 'clock_out') {
            startContainer.style.display = 'none';
            endContainer.style.display = 'block';
        } else {
            startContainer.style.display = 'block';
            endContainer.style.display = 'block';
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleTimeFields();
    });
</script>
@endpush
