@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ isset($cheque) ? __('Edit Post-Dated Cheque') : __('Add Post-Dated Cheque') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('post-dated-cheques.index') }}">{{ __('Post-Dated Cheques') }}</a></li>
            <li class="breadcrumb-item active">{{ isset($cheque) ? __('Edit') : __('Add') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ isset($cheque) ? __('Edit Post-Dated Cheque') : __('Add Post-Dated Cheque') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($cheque) ? route('post-dated-cheques.update', $cheque->id) : route('post-dated-cheques.store') }}">
                        @csrf
                        @if(isset($cheque))
                            @method('PUT')
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Cheque Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="cheque_number" class="form-control" value="{{ $cheque->cheque_number ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select name="is_received" class="form-select" required>
                                        <option value="1" {{ (isset($cheque) && $cheque->is_received) ? 'selected' : '' }}>{{ __('Received') }}</option>
                                        <option value="0" {{ (isset($cheque) && !$cheque->is_received) ? 'selected' : '' }}>{{ __('Issued') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Cheque Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="cheque_date" class="form-control" value="{{ $cheque->cheque_date ?? date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Due Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" class="form-control" value="{{ $cheque->due_date ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ $cheque->amount ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Bank Account') }} <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" class="form-select" required>
                                        <option value="">{{ __('Select Bank Account') }}</option>
                                        @foreach($bank_accounts as $id => $name)
                                            <option value="{{ $id }}" {{ (isset($cheque) && $cheque->bank_account_id == $id) ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ $cheque->remarks ?? '' }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('post-dated-cheques.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ isset($cheque) ? __('Update') : __('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
