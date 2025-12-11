@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Accounts') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Accounts') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="javascript:void(0)" data-url="{{ route('accounting.accounts.create') }}" class="btn add-btn"
                        data-ajax-modal="true" data-size="lg" data-title="Add New Account">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Account') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Filters -->
        <div class="row filter-row">
            <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus">
                    <select class="form-control floating" id="filter_account_type">
                        <option value="">-- {{ __('All Types') }} --</option>
                        @foreach($accountTypes as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <label class="focus-label">{{ __('Account Type') }}</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus">
                    <select class="form-control floating" id="filter_account_group">
                        <option value="">-- {{ __('All Groups') }} --</option>
                        @foreach($accountGroups as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <label class="focus-label">{{ __('Account Group') }}</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="input-block mb-3 form-focus">
                    <input type="text" class="form-control floating" id="filter_account_name">
                    <label class="focus-label">{{ __('Account Name') }}</label>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <button class="btn btn-success w-100" id="btn_filter">{{ __('Search') }}</button>
            </div>
        </div>
        <!-- /Filters -->

        <!-- Accounts Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
                </div>
            </div>
        </div>
        <!-- /Accounts Table -->

    </div>
@endsection

@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}

<script type="module">
    // Custom filter functionality
    $('#btn_filter').click(function() {
        window.LaravelDataTables["accounts-table"].ajax.reload();
    });
    
    $('.filter-row input, .filter-row select').on('keypress change', function(e) {
        if (e.which === 13 || e.type === 'change') {
            window.LaravelDataTables["accounts-table"].ajax.reload();
        }
    });
</script>
@endpush
