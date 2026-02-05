@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header">
            <h4><i class="fa fa-plus"></i> {{ __('Create Accrual Plan') }}</h4>
        </div>
        
        <form action="{{ route('leave.config.accrual-plans.store') }}" method="POST">
            @csrf
            
            <div class="row">
                {{-- Basic Information --}}
                <div class="col-md-12 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Plan Details') }}</h5>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Plan Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g. Standard Annual Leave Plan">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Target Leave Type') }} <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">{{ __('Select Leave Type') }}</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Accrual Rules --}}
                <div class="col-md-6 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Accrual Rules') }}</h5>
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Accrual Frequency') }} <span class="text-danger">*</span></label>
                                <select name="accrual_frequency" class="form-select" required>
                                    <option value="monthly" {{ old('accrual_frequency') == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                    <option value="yearly" {{ old('accrual_frequency') == 'yearly' ? 'selected' : '' }}>{{ __('Yearly (Start of Year)') }}</option>
                                    {{-- <option value="per_pay_period" {{ old('accrual_frequency') == 'per_pay_period' ? 'selected' : '' }}>{{ __('Per Pay Period') }}</option> --}}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Accrual Rate (Days)') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="accrual_rate" class="form-control" value="{{ old('accrual_rate') }}" required placeholder="e.g. 1.5">
                                <small class="text-muted">{{ __('Days earned per frequency period') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Max Accrual Days') }}</label>
                                <input type="number" name="max_accrual_days" class="form-control" value="{{ old('max_accrual_days') }}" placeholder="Leave empty for unlimited">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Waiting Period (Days)') }}</label>
                                <input type="number" name="waiting_period_days" class="form-control" value="{{ old('waiting_period_days', 0) }}" min="0">
                                <small class="text-muted">{{ __('Days before accrual starts') }}</small>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="prorate_on_join" id="prorate_on_join" value="1" {{ old('prorate_on_join', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prorate_on_join">{{ __('Prorate on Join Date') }}</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Carryover & Negative Balance --}}
                <div class="col-md-6 mb-4">
                    <h5 class="text-primary border-bottom pb-2">{{ __('Carryover & Limits') }}</h5>
                    <div class="mt-3">
                        {{-- Carryover --}}
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="allow_carryover" id="allow_carryover" value="1" {{ old('allow_carryover') ? 'checked' : '' }} onchange="toggleCarryover()">
                                <label class="form-check-label fw-bold" for="allow_carryover">{{ __('Allow Carryover') }}</label>
                            </div>
                            <div id="carryover_fields" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Max Carryover Days') }}</label>
                                    <input type="number" name="max_carryover_days" class="form-control" value="{{ old('max_carryover_days') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Expiry Date (Optional)') }}</label>
                                    <input type="date" name="carryover_expiry_date" class="form-control" value="{{ old('carryover_expiry_date') }}">
                                    <small class="text-muted">{{ __('Carried over days expire on this date next year') }}</small>
                                </div>
                            </div>
                        </div>

                        {{-- Negative Balance --}}
                        <div class="p-3 bg-light rounded">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="allow_negative_balance" id="allow_negative_balance" value="1" {{ old('allow_negative_balance') ? 'checked' : '' }} onchange="toggleNegative()">
                                <label class="form-check-label fw-bold" for="allow_negative_balance">{{ __('Allow Negative Balance') }}</label>
                            </div>
                            <div id="negative_fields" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Max Negative Days') }}</label>
                                    <input type="number" name="max_negative_days" class="form-control" value="{{ old('max_negative_days', 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('leave.config.accrual-plans.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">{{ __('Save Accrual Plan') }}</button>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
    function toggleCarryover() {
        const allowed = document.getElementById('allow_carryover').checked;
        if (allowed) {
            $('#carryover_fields').slideDown();
        } else {
            $('#carryover_fields').slideUp();
        }
    }

    function toggleNegative() {
        const allowed = document.getElementById('allow_negative_balance').checked;
        if (allowed) {
            $('#negative_fields').slideDown();
        } else {
            $('#negative_fields').slideUp();
        }
    }

    // Initialize state
    document.addEventListener('DOMContentLoaded', function() {
        toggleCarryover();
        toggleNegative();
    });
</script>
@endpush
@endsection
