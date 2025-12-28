@extends('layouts.app')

@push('page-scripts')
    <!-- Include Date Range Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="module">
        $(document).ready(function() {
            // Dynamically load daterangepicker to ensure it uses the global jQuery instance from app.js
            $.getScript("https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js", function() {
                // Trigger event so other scripts know it's ready
                $(document).trigger('daterangepicker:ready');
            });
        });
    </script>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">

            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    <a href="#">{{ __('Accounting') }}</a>
                </li>
            </ul>

        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Tabs Navigation -->
        <div class="row mb-1">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#account-settings-tab" data-bs-toggle="tab">
                            <i class="fa fa-cogs"></i> {{ __('Account Settings') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#deposits-transfers-tab" data-bs-toggle="tab">
                            <i class="fa fa-exchange-alt"></i> {{ __('Deposits & Transfers') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cheques-ob-tab" data-bs-toggle="tab">
                            <i class="fa fa-money-check"></i> {{ __('Cheques in Hand') }}
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
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-12 text-end">
                                        <button type="button" class="btn btn-success btn-sm me-2"
                                            data-url="{{ route('account.deposit', ['type' => 'cash']) }}"
                                            data-ajax-modal="true" data-title="Cash Deposit">
                                            <i class="fa fa-money-bill"></i> Cash Deposit
                                        </button>

                                        <button type="button" class="btn btn-primary btn-sm me-2"
                                            data-url="{{ route('account.cheque-deposit') }}" data-ajax-modal="true"
                                            data-title="Cheque Deposit">
                                            <i class="fa fa-money-check"></i> Cheque Deposit
                                        </button>

                                        <button type="button" class="btn btn-warning btn-sm me-2"
                                            data-url="{{ route('account.deposit', ['type' => 'card']) }}"
                                            data-ajax-modal="true" data-title="Card Deposit">
                                            <i class="fa fa-credit-card"></i> Card Deposit
                                        </button>

                                        <a data-url="{{ route('accounts.create') }}" href="javascript:void(0)"
                                            class="btn btn-warning btn-sm" data-ajax-modal="true" data-size="lg"
                                            data-title="Add Account"
                                            style="background-color: #fd7e14; border-color: #fd7e14; color: white;">
                                            <i class="fa-solid fa-plus"></i> Add Account
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Section -->
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <h5><i class="fa fa-filter"></i> {{ __('Filters') }}</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Account Type') }}:</label>
                                            <select id="account_type_filter" class="form-select select2"
                                                style="width: 100%;">
                                                <option value="">{{ __('All') }}</option>
                                                @foreach ($account_types as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Account Sub Type') }}:</label>
                                            <select id="account_sub_type_filter" class="form-select select2"
                                                style="width: 100%;">
                                                <option value="">{{ __('All') }}</option>
                                                @if (isset($account_sub_types))
                                                    @foreach ($account_sub_types as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Account Group') }}:</label>
                                            <select id="account_group_filter" class="form-select select2"
                                                style="width: 100%;">
                                                <option value="">{{ __('All') }}</option>
                                                @foreach ($account_groups as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Account Name') }}:</label>
                                            <select id="account_name_filter" class="form-select select2"
                                                style="width: 100%;">
                                                <option value="">{{ __('All') }}</option>
                                                @if (isset($accounts))
                                                    @foreach ($accounts as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Accounts Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0 datatable w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Account Type') }}</th>
                                                <th>{{ __('Account Group') }}</th>
                                                <th>{{ __('Account Number') }}</th>
                                                <th>{{ __('Balance') }}</th>
                                                <th class="text-end" data-orderable="false">{{ __('Action') }}</th>
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
                                                <th>ID</th>
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
                                                <th>ID</th>
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

            <!-- Account Settings Tab -->
            <div class="tab-pane fade" id="account-settings-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Account Setting') }}</h5>

                                <a data-url="{{ route('account-settings.create') }}" href="javascript:void(0)"
                                    class="btn btn-primary btn-sm" data-ajax-modal="true" data-size="md"
                                    data-title="{{ __('Add Account Setting') }}">
                                    <i class="fa-solid fa-plus"></i> {{ __('Add Setting') }}
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0" id="settings-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Account') }}</th>
                                                <th>{{ __('Group') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Settings') }}</th>
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

            <!-- Deposits & Transfers Tab -->
            <div class="tab-pane fade" id="deposits-transfers-tab">
                @includeIf('accounting::accounts.list_deposit_transfer')
            </div>

            <!-- Cheques in Hand Tab -->
            <div class="tab-pane fade" id="cheques-ob-tab">
                @includeIf('accounting::accounts.cheques_opening_balance_details')
            </div>
        </div><!-- /Tab Content -->

        <div class="modal fade view_modal" tabindex="-1" role="dialog"></div>


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
                    url: "{{ route('accounts.index') }}",
                    data: function(d) {
                        d.account_type = $('#account_type_filter').val();

                        d.account_group = $('#account_group_filter').val();
                        d.account_name = $('#account_name_filter').val();
                    }
                },
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
                        data: 'account_type',
                        name: 'account_type'
                    },

                    {
                        data: 'account_group',
                        name: 'account_group'
                    },

                    {
                        data: 'account_number',
                        name: 'account_number'
                    },
                    {
                        data: 'balance',
                        name: 'balance',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
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


            var settingsTable = null;

            /* Load DataTable only when tab is opened */
            $('a[href="#account-settings-tab"]').on('shown.bs.tab', function() {

                if (settingsTable === null) {

                    settingsTable = $('#settings-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('account-settings.index') }}",
                        order: [
                            [0, 'desc']
                        ],
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'type',
                                name: 'key'
                            },
                            {
                                data: 'date',
                                name: 'date'
                            },
                            {
                                data: 'account',
                                name: 'account_id'
                            },
                            {
                                data: 'group',
                                name: 'group_id'
                            },
                            {
                                data: 'amount',
                                name: 'amount'
                            },
                            {
                                data: 'settings',
                                name: 'settings',
                                orderable: false
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });

                    /* Initialize popovers AFTER every draw */
                    $('#settings-table').on('draw.dt', function() {
                        $('[data-bs-toggle="popover"]').popover({
                            trigger: 'hover',
                            placement: 'left',
                            html: true,
                            container: 'body'
                        });
                    });
                }
            });

        });

        function deleteAccountSetting(id) {

            if (!confirm('{{ __('Are you sure you want to delete this record?') }}')) {
                return;
            }

            $.ajax({
                url: '/accounting/account-settings/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        settingsTable.ajax.reload(null, false);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('{{ __('Something went wrong!') }}');
                }
            });
        }

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


        $(document).on('click', '.btn-modal', function() {
            let container = $(this).data('container');
            let url = $(this).data('href');

            $(container).html(
                '<div class="modal-dialog"><div class="modal-content p-5 text-center">Loading...</div></div>');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $(container).html(response);
                    $(container).modal('show');
                },
                error: function() {
                    alert('Failed to load modal');
                }
            });
        });

        // Handle Account Type Form Submission
        $(document).on('submit', '#typeForm', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.attr('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    btn.attr('disabled', false);
                    if (response.success) {
                        Toastify({
                            text: response.msg || response.message || "{{ __('messages.success') }}",
                            className: "success",
                        }).showToast();
                        $('#generalModalPopup').modal('hide');
                        $('#types-table').DataTable().ajax.reload();
                    } else {
                        Toastify({
                            text: response.msg || response.message || "{{ __('messages.error') }}",
                            className: "danger",
                        }).showToast();
                    }
                },
                error: function(xhr) {
                    btn.attr('disabled', false);
                    let errorMsg = 'An error occurred';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Toastify({
                        text: errorMsg,
                        className: "danger",
                    }).showToast();
                }
            });
        });

        // Handle Account Group Form Submission
        $(document).on('submit', '#groupForm', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.attr('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    btn.attr('disabled', false);
                    if (response.success) {
                        Toastify({
                            text: response.msg || response.message || "{{ __('messages.success') }}", // Handle various response keys
                            className: "success",
                        }).showToast();
                        $('#generalModalPopup').modal('hide');
                        $('#groups-table').DataTable().ajax.reload();
                    } else {
                        Toastify({
                            text: response.msg || response.message || "{{ __('messages.error') }}",
                            className: "danger",
                        }).showToast();
                    }
                },
                error: function(xhr) {
                    btn.attr('disabled', false);
                    let errorMsg = 'An error occurred';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Toastify({
                        text: errorMsg,
                        className: "danger",
                    }).showToast();
                }
            });
        });
    </script>
@endpush
