@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header">
            <h4><i class="fa fa-plus"></i> {{ __('New Leave Allocation') }}</h4>
        </div>
        
        <form action="{{ route('leave.management.allocations.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Employee') }} <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-control select" required>
                        <option value="">{{ __('Select Employee') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->firstname }} {{ $user->lastname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Leave Type') }} <span class="text-danger">*</span></label>
                    <select name="leave_type_id" class="form-select" required>
                        <option value="">{{ __('Select Leave Type') }}</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Allocation Type --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Allocation Type') }} <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="allocation_type" id="type_manual" value="manual" {{ old('allocation_type', 'manual') == 'manual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_manual">{{ __('Regular Allocation') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="allocation_type" id="type_accrual" value="accrual" {{ old('allocation_type') == 'accrual' ? 'checked' : '' }}>
                            <label class="form-check-label" for="type_accrual">{{ __('Accrual Allocation') }}</label>
                        </div>
                    </div>
                </div>

                {{-- Accrual Plan (Conditional) --}}
                <div class="col-md-6 mb-3" id="accrual_plan_div" style="display: none;">
                    <label class="form-label">{{ __('Accrual Plan') }} <span class="text-danger">*</span></label>
                    <select name="accrual_plan_id" class="form-select">
                        <option value="">{{ __('Select Accrual Plan') }}</option>
                        @foreach($accrualPlans as $plan)
                            <option value="{{ $plan->id }}" {{ old('accrual_plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Start Date --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                </div>

                {{-- Run Until --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Run Until') }} <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-2 mb-2">
                         <div class="form-check">
                            <input class="form-check-input" type="radio" name="run_until_option" id="run_no_limit" value="no_limit" {{ old('run_until_option', 'no_limit') == 'no_limit' ? 'checked' : '' }}>
                            <label class="form-check-label" for="run_no_limit">{{ __('No Limit') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="run_until_option" id="run_specific_date" value="date" {{ old('run_until_option') == 'date' ? 'checked' : '' }}>
                            <label class="form-check-label" for="run_specific_date">{{ __('Specific Date') }}</label>
                        </div>
                    </div>
                    <input type="date" name="run_until_date" id="run_until_date_input" class="form-control" value="{{ old('run_until_date') }}" style="display: none;">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Allocated Days') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" name="allocated_days" class="form-control" value="{{ old('allocated_days', 0) }}" required min="0">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('leave.management.allocations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">{{ __('Save Allocation') }}</button>
            </div>
        </form>
    </div>
</div>

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Accrual Plan
        const typeRadios = document.querySelectorAll('input[name="allocation_type"]');
        const accrualDiv = document.getElementById('accrual_plan_div');
        
        function toggleAccrual() {
             const selected = document.querySelector('input[name="allocation_type"]:checked').value;
             if(selected === 'accrual') {
                 accrualDiv.style.display = 'block';
             } else {
                 accrualDiv.style.display = 'none';
             }
        }
        
        typeRadios.forEach(radio => radio.addEventListener('change', toggleAccrual));
        toggleAccrual(); // Init
        
        // Toggle Run Until Date
        const runRadios = document.querySelectorAll('input[name="run_until_option"]');
        const runDateInput = document.getElementById('run_until_date_input');
        
        function toggleRunDate() {
            const selected = document.querySelector('input[name="run_until_option"]:checked').value;
             if(selected === 'date') {
                 runDateInput.style.display = 'block';
             } else {
                 runDateInput.style.display = 'none';
             }
        }
        runRadios.forEach(radio => radio.addEventListener('change', toggleRunDate));
        toggleRunDate(); // Init
    });
</script>
@endpush
@endsection
