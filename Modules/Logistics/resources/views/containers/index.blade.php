@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Containers</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Containers</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.containers.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Add Container</a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-cubes"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalContainers }}</h3>
                        <span>Total Containers</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-truck"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $inTransit }}</h3>
                        <span>In Transit</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-anchor"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $atDjibouti }}</h3>
                        <span>At Djibouti</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $demurrageRisk }}</h3>
                        <span class="text-danger">Demurrage Risk</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table" id="containers-table">
                    <thead>
                        <tr>
                            <th>Container No</th>
                            <th>Shipment</th>
                            <th>Size</th>
                            <th>Type</th>
                            <th>Seal No</th>
                            <th>Status</th>
                            <th>Location</th>
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
        if ($('#containers-table').length > 0) {
            $('#containers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('logistics.containers.index') }}",
                columns: [
                    {data: 'container_no', name: 'container_no'},
                    {data: 'shipment_no', name: 'shipment_no'},
                    {data: 'size', name: 'size'},
                    {data: 'type', name: 'type'},
                    {data: 'seal_no', name: 'seal_no'},
                    {data: 'status', name: 'status'},
                    {data: 'location', name: 'location'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush
@endsection
