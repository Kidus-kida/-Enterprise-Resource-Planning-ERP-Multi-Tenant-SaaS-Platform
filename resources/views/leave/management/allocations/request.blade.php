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
                        <li class="breadcrumb-item"><a href="{{ route('leave.my-time') }}">{{ __('My Time') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Request Allocation') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('leave.management.allocations.store-request') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="leave_type_id" class="form-label">{{ __('Leave Type') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('leave_type_id') is-invalid @enderror" id="leave_type_id" name="leave_type_id" required>
                                        <option value="">{{ __('Select Leave Type') }}</option>
                                        @foreach($leaveTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="allocated_days" class="form-label">{{ __('Requested Days') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.5" class="form-control @error('allocated_days') is-invalid @enderror" 
                                        id="allocated_days" name="allocated_days" value="{{ old('allocated_days') }}" required>
                                    @error('allocated_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">{{ __('Reason / Justification') }} <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                        id="notes" name="notes" rows="4" required placeholder="Explain why you need this additional allocation...">{{ old('notes') }}</textarea>
                                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">{{ __('Submit Request') }}</button>
                                <a href="{{ route('leave.my-time') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
