@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Documents</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.documents.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Upload Document</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-file-text"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalDocuments }}</h3>
                        <span>Total Documents</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $pendingDocuments }}</h3>
                        <span>Pending Approval</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $approvedDocuments }}</h3>
                        <span>Approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table" id="documents-table">
                    <thead>
                        <tr>
                            <th>Document Name</th>
                            <th>Type</th>
                            <th>Shipment</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        if ($('#documents-table').length > 0) {
            $('#documents-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('logistics.documents.index') }}",
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'type', name: 'type', render: function(data){
                         return data.replace('_', ' ').toUpperCase();
                    }},
                    {data: 'shipment_no', name: 'shipment_no'},
                    {data: 'file_size', name: 'file_size'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush
@endsection
