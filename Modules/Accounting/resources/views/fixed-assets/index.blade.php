@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Fixed Assets') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Fixed Assets') }}</li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a href="{{ route('fixed-asset.create') }}" class="btn add-btn">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Fixed Asset') }}
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
                                        <th>{{ __('Asset Name') }}</th>
                                        <th>{{ __('Asset Code') }}</th>
                                        <th>{{ __('Purchase Date') }}</th>
                                        <th class="text-end">{{ __('Purchase Cost') }}</th>
                                        <th class="text-end">{{ __('Current Value') }}</th>
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
        
        $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: "{{route('fixed-asset.index')}}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'code', name: 'code'},
                {data: 'purchase_date', name: 'purchase_date'},
                {data: 'purchase_cost', name: 'purchase_cost', className: 'text-end'},
                {data: 'current_value', name: 'current_value', className: 'text-end'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });
</script>
@endpush
