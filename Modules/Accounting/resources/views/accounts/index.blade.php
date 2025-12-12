@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Chart of Accounts') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    <a href="#">{{ __('Accounting') }}</a>
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a data-url="{{ route('account.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true" data-size="lg" data-title="{{ __('Add Account') }}">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Account') }}
                    </a>
                    <a href="{{ route('accounts.import') }}" class="btn btn-primary">
                        <i class="fa-solid fa-file-import"></i> {{ __('Import Accounts') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Tabs Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-tabs nav-tabs-solid">
                    <li class="nav-item">
                        <a class="nav-link active" href="#accounts-tab" data-bs-toggle="tab">
                            <i class="fa fa-list"></i> {{ __('Accounts') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#account-types-tab" data-bs-toggle="tab">
                            <i class="fa fa-tags"></i> {{ __('Account Types') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#account-groups-tab" data-bs-toggle="tab">
                            <i class="fa fa-folder"></i> {{ __('Account Groups') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <!-- Accounts Tab -->
            <div class="tab-pane fade show active" id="accounts-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Filter Section -->
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select id="account_type_filter" class="form-select">
                                            <option value="">{{ __('All Account Types') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="account_group_filter" class="form-select">
                                            <option value="">{{ __('All Account Groups') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="status_filter" class="form-select">
                                            <option value="active">{{ __('Active Accounts') }}</option>
                                            <option value="all">{{ __('All Accounts') }}</option>
                                            <option value="closed">{{ __('Closed Accounts') }}</option>
                                            <option value="disabled">{{ __('Disabled Accounts') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Accounts Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0 datatable w-100">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Account Number') }}</th>
                                                <th>{{ __('Account Name') }}</th>
                                                <th>{{ __('Account Type') }}</th>
                                                <th>{{ __('Account Group') }}</th>
                                                <th>{{ __('Current Balance') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /Accounts Tab -->

            <!-- Account Types Tab -->
            <div class="tab-pane fade" id="account-types-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Account Types') }}</h5>
                                <a data-url="{{ route('account-types.create') }}" href="javascript:void(0)"
                                    class="btn btn-primary btn-sm" data-ajax-modal="true" data-size="md"
                                    data-title="{{ __('Add Account Type') }}">
                                    <i class="fa-solid fa-plus"></i> {{ __('Add Type') }}
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0" id="types-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /Account Types Tab -->

            <!-- Account Groups Tab -->
            <div class="tab-pane fade" id="account-groups-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Account Groups') }}</h5>
                                <a data-url="{{ route('account-groups.create') }}" href="javascript:void(0)"
                                    class="btn btn-primary btn-sm" data-ajax-modal="true" data-size="md"
                                    data-title="{{ __('Add Account Group') }}">
                                    <i class="fa-solid fa-plus"></i> {{ __('Add Group') }}
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0" id="groups-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /Account Groups Tab -->
        </div><!-- /Tab Content -->
    </div>
@endsection


@push('page-scripts')
    @vite(['resources/js/datatables.js'])
    <script type="module">
        $(document).ready(function() {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            var table = $('.datatable').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: {
                    url: "{{ route('account.index') }}",
                    data: function(d) {
                        d.account_type = $('#account_type_filter').val();
                        d.account_group = $('#account_group_filter').val();
                        d.status = $('#status_filter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'account_number',
                        name: 'account_number'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'account_type',
                        name: 'account_type'
                    },
                    {
                        data: 'account_group',
                        name: 'account_group'
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                        className: 'text-end'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                initComplete: function() {
                    $('tr>td:last-child').addClass('text-end')
                }
            });

            // Reload table on filter change
            $('#account_type_filter, #account_group_filter, #status_filter').change(function() {
                table.draw();
            });

            // Account Types DataTable
            var typesTable = null;
            $('a[href="#account-types-tab"]').on('shown.bs.tab', function() {
                if (typesTable === null) {
                    typesTable = $('#types-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('account-types.index') }}",
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'description',
                                name: 'description'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ]
                    });
                }
            });

            // Account Groups DataTable
            var groupsTable = null;
            $('a[href="#account-groups-tab"]').on('shown.bs.tab', function() {
                if (groupsTable === null) {
                    groupsTable = $('#groups-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('account-groups.index') }}",
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'description',
                                name: 'description'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                        ]
                    });
                }
            });
        });

        // Delete functions
        function deleteType(id) {
            if (confirm('Are you sure you want to delete this account type?')) {
                $.ajax({
                    url: '/accounting/account-types/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#types-table').DataTable().ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            }
        }

        function deleteGroup(id) {
            if (confirm('Are you sure you want to delete this account group?')) {
                $.ajax({
                    url: '/accounting/account-groups/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#groups-table').DataTable().ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            }
        }
    </script>
@endpush
