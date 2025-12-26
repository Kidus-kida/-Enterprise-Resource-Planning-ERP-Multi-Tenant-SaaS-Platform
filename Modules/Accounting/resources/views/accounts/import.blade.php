@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Import Accounts') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('account.index') }}">{{ __('Accounts') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Import') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Import Accounts') }}</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> {{ __('Upload an Excel or CSV file with account data. The file should include columns for: Account Number, Account Name, Account Type, Account Group, Opening Balance.') }}
                    </div>

                    <form method="POST" action="{{ route('accounts.post-import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('Select File') }} <span class="text-danger">*</span></label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="form-text text-muted">{{ __('Accepted formats: Excel (.xlsx, .xls) or CSV (.csv)') }}</small>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('account.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Import') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
