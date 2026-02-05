@extends('layouts.app')

@section('title', $pageTitle)

@push('page-styles')
<style>
    .shift-step-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .step-number {
        background: #34444c;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        flex-shrink: 0;
    }
    .remove-step {
        color: #dc3545;
        cursor: pointer;
        font-size: 20px;
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">
        
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('shifts.rotation.index') }}" class="btn btn-outline-secondary">
                        <i class="la la-arrow-left"></i> {{ __('Back to List') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('shifts.rotation.update', $rotation->id) }}" method="POST" id="rotationForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Plan Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $rotation->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Frequency Type') }} <span class="text-danger">*</span></label>
                                        <select name="frequency_type" class="form-select @error('frequency_type') is-invalid @enderror" required>
                                            <option value="daily" {{ old('frequency_type', $rotation->frequency_type) == 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
                                            <option value="weekly" {{ old('frequency_type', $rotation->frequency_type) == 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                                            <option value="monthly" {{ old('frequency_type', $rotation->frequency_type) == 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                                        </select>
                                        @error('frequency_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Frequency Interval') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="frequency_interval" class="form-control @error('frequency_interval') is-invalid @enderror" 
                                                   value="{{ old('frequency_interval', $rotation->frequency_interval) }}" min="1" required>
                                            <span class="input-group-text" id="interval-label">{{ __('Week(s)') }}</span>
                                        </div>
                                        @error('frequency_interval')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Rotation Start Date') }} <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                               value="{{ old('start_date', $rotation->start_date->format('Y-m-d')) }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Status') }}</label>
                                        <select name="is_active" class="form-select">
                                            <option value="1" {{ old('is_active', $rotation->is_active) ? 'selected' : '' }}>{{ __('Active') }}</option>
                                            <option value="0" {{ !old('is_active', $rotation->is_active) ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('Description') }}</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description', $rotation->description) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('Rotation Sequence') }} <span class="text-danger">*</span></h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addShiftBtn">
                                    <i class="la la-plus"></i> {{ __('Add Shift to Sequence') }}
                                </button>
                            </div>

                            <div id="shiftsContainer">
                                @php $currentShifts = old('shifts', $rotation->steps->pluck('shift_id')->toArray()); @endphp
                                @foreach($currentShifts as $index => $shiftId)
                                    <div class="shift-step-row">
                                        <div class="step-number">{{ $index + 1 }}</div>
                                        <div class="flex-grow-1">
                                            <select name="shifts[]" class="form-select @error('shifts.'.$index) is-invalid @enderror" required>
                                                <option value="">{{ __('Rest Day / Off Day') }}</option>
                                                @foreach($shifts as $shift)
                                                    <option value="{{ $shift->id }}" {{ $shiftId == $shift->id ? 'selected' : '' }}>
                                                        {{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="remove-step" title="{{ __('Remove Step') }}"><i class="la la-times-circle"></i></div>
                                    </div>
                                @endforeach
                            </div>
                            @error('shifts')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <div class="submit-section mt-4">
                                <button class="btn btn-primary submit-btn w-100" type="submit">{{ __('Update Rotation Plan') }}</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('shiftsContainer');
        const addBtn = document.getElementById('addShiftBtn');
        const freqType = document.querySelector('select[name="frequency_type"]');
        const intervalLabel = document.getElementById('interval-label');

        function updateLabels() {
            const val = freqType.value;
            if (val === 'daily') intervalLabel.textContent = 'Day(s)';
            else if (val === 'weekly') intervalLabel.textContent = 'Week(s)';
            else if (val === 'monthly') intervalLabel.textContent = 'Month(s)';
        }
        freqType.addEventListener('change', updateLabels);
        updateLabels();

        function renumberSteps() {
            container.querySelectorAll('.step-number').forEach((el, i) => {
                el.textContent = i + 1;
            });
        }

        addBtn.addEventListener('click', function() {
            const firstRow = container.querySelector('.shift-step-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('select').value = '';
            newRow.querySelector('select').classList.remove('is-invalid');
            container.appendChild(newRow);
            renumberSteps();
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-step')) {
                const rows = container.querySelectorAll('.shift-step-row');
                if (rows.length > 1) {
                    e.target.closest('.shift-step-row').remove();
                    renumberSteps();
                } else {
                    alert('At least one shift is required in the sequence.');
                }
            }
        });
    });
</script>
@endpush
