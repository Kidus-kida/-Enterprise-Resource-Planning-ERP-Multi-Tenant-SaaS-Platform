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
                        <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('shifts.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">{{ __('Shift Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           placeholder="{{ __('e.g., Morning Shift') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">{{ __('Shift Code') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code') }}" 
                                           placeholder="{{ __('e.g., MORNING') }}" required>
                                    <small class="text-muted">{{ __('Unique identifier for this shift') }}</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label">{{ __('Start Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', '08:00') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_time" class="form-label">{{ __('End Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', '17:00') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="grace_period_minutes" class="form-label">{{ __('Clock-In Grace (minutes)') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="grace_period_minutes" id="grace_period_minutes" 
                                           class="form-control @error('grace_period_minutes') is-invalid @enderror" 
                                           value="{{ old('grace_period_minutes', $defaultGracePeriod ?? '15') }}" 
                                           min="0" max="60" required>
                                    <small class="text-muted">{{ __('Minutes employees can be late without penalty') }}</small>
                                    @error('grace_period_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="grace_out_minutes" class="form-label">{{ __('Clock-Out Grace (minutes)') }}</label>
                                    <input type="number" name="grace_out_minutes" id="grace_out_minutes" 
                                           class="form-control @error('grace_out_minutes') is-invalid @enderror" 
                                           value="{{ old('grace_out_minutes', $defaultGraceOut ?? '10') }}" 
                                           min="0" max="60">
                                    <small class="text-muted">{{ __('Minutes employees can leave early without penalty') }}</small>
                                    @error('grace_out_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            {{ __('Active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('Work Days') }}</label>
                                <div class="row">
                                    @php
                                        $days = [
                                            1 => 'Monday',
                                            2 => 'Tuesday',
                                            3 => 'Wednesday',
                                            4 => 'Thursday',
                                            5 => 'Friday',
                                            6 => 'Saturday',
                                            7 => 'Sunday',
                                        ];
                                    @endphp
                                    @foreach($days as $value => $day)
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="work_days[]" 
                                                       value="{{ $value }}" id="day_{{$value}}"
                                                       {{ in_array($value, old('work_days', $defaultWorkDays ?? [1,2,3,4,5])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $value }}">
                                                    {{ __($day) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" id="description" rows="3" 
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="{{ __('Optional description for this shift') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-end">
                                <a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary">
                                    <i class="la la-times"></i> {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-save"></i> {{ __('Create Shift') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
