@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ isset($journal) ? __('Edit Journal Entry') : __('New Journal Entry') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('journal.index') }}">{{ __('Journals') }}</a></li>
            <li class="breadcrumb-item active">{{ isset($journal) ? __('Edit') : __('Create') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ isset($journal) ? __('Edit Journal Entry') : __('New Journal Entry') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($journal) ? route('journal.update', $journal->id) : route('journal.store') }}">
                        @csrf
                        @if(isset($journal))
                            @method('PUT')
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ $journal->date ?? date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Journal Number') }}</label>
                                    <input type="text" name="journal_no" class="form-control" value="{{ $journal->journal_no ?? '' }}" placeholder="Auto-generated">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Account') }} <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select" required>
                                        <option value="">{{ __('Select Account') }}</option>
                                        @foreach($accounts as $id => $name)
                                            <option value="{{ $id }}" {{ (isset($journal) && $journal->account_id == $id) ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="debit" {{ (isset($journal) && $journal->type == 'debit') ? 'selected' : '' }}>{{ __('Debit') }}</option>
                                        <option value="credit" {{ (isset($journal) && $journal->type == 'credit') ? 'selected' : '' }}>{{ __('Credit') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount" class="form-control" value="{{ $journal->amount ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ $journal->description ?? '' }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('journal.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ isset($journal) ? __('Update') : __('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
