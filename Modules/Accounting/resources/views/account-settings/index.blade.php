@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Account Settings') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Account Settings') }}</li>
            </ul>

            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a data-url="{{ route('account-settings.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true" data-size="md" data-title="{{ __('Add Account Setting') }}">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Setting') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
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

    </div>
@endsection
@push('page-scripts')
    @vite(['resources/js/datatables.js'])

    <script type="module">
        $(document).ready(function() {

            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }

            $('.datatable').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ajax: "{{ route('account-settings.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'account',
                        name: 'account'
                    },
                    {
                        data: 'group',
                        name: 'group'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'settings',
                        name: 'settings'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });

        /* Delete */
        function deleteAccountSetting(id) {
            if (!confirm('{{ __('Are you sure you want to delete this record?') }}')) return;

            $.ajax({
                url: '/accounting/account-settings/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('.datatable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    </script>
@endpush
