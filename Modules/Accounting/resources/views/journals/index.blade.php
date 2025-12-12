@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Journal Entries') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Journals') }}</li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('journal.create') }}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Journal Entry') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Date Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>{{ __('Start Date') }}</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>{{ __('End Date') }}</label>
                                <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Journals Table -->
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0 datatable w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Journal No') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                        <th>{{ __('Created By') }}</th>
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
    </div>
@endsection

@push('page-scripts')
@vite(["resources/js/datatables.js"])
<script type="module">
    $(document).ready(function(){
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('.datatable')) {
            $('.datatable').DataTable().destroy();
        }
        
        var table = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: "{{route('journal.index')}}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'date', name: 'date'},
                {data: 'journal_no', name: 'journal_no'},
                {data: 'description', name: 'description'},
                {data: 'amount', name: 'amount', className: 'text-end'},
                {data: 'created_by', name: 'created_by'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        // Reload on date change
        $('#start_date, #end_date').change(function(){
            table.draw();
        });
    });
</script>
@endpush
