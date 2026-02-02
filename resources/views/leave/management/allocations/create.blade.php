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

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Year') }} <span class="text-danger">*</span></label>
                    <select name="year" class="form-select" required>
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear - 1; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
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
@endsection
