@extends('layouts.app')
@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Shipments</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Shipments</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.shipments.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add Shipment</a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table" id="shipments-table">
                    <thead>
                        <tr>
                            <th>Shipment No</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Transport Mode</th>
                            <th>ETA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script type="module">
    $(document).ready(function() {
        if ($('#shipments-table').length > 0) {
            $('#shipments-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('logistics.shipments.index') }}",
                columns: [
                    {data: 'shipment_no', name: 'shipment_no'},
                    {data: 'vendor', name: 'vendor'},
                    {data: 'status', name: 'status'},
                    {data: 'transport_mode', name: 'transport_mode'},
                    {data: 'expected_arrival', name: 'expected_arrival'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush
@endsection
