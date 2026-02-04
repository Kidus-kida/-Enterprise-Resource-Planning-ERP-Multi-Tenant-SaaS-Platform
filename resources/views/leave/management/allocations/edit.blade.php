@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header">
            <h4><i class="fa fa-pencil"></i> {{ __('Edit Leave Allocation') }}</h4>
        </div>
        
        <form action="{{ route('leave.management.allocations.update', $allocation->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Employee') }}</label>
                    <input type="text" class="form-control" value="{{ $allocation->user->firstname ?? '' }} {{ $allocation->user->lastname ?? '' }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Leave Type') }}</label>
                    <input type="text" class="form-control" value="{{ $allocation->leaveType->type_name ?? '' }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Year') }}</label>
                    <input type="text" class="form-control" value="{{ $allocation->year }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Allocated Days') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" name="allocated_days" class="form-control" value="{{ old('allocated_days', $allocation->allocated_days) }}" required min="0">
                    <small class="text-muted">{{ __('Current Available Balance: ') }} {{ $allocation->available_days }}</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $allocation->notes) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('leave.management.allocations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">{{ __('Update Allocation') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
