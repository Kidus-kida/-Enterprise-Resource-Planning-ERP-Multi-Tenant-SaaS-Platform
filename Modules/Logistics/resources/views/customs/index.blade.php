@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Customs Declarations</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Customs Declarations</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.customs.create') }}" class="btn add-btn"><i class="fa fa-plus"></i> Create Declaration</a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-file-text"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalDeclarations }}</h3>
                        <span>Total Declarations</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-hourglass-half"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $pending }}</h3>
                        <span>Pending</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $cleared }}</h3>
                        <span>Cleared</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
             <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ number_format($totalDutyPaid, 0) }}</h3>
                        <span>Total Duties (ETB)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table" id="customs-table">
                    <thead>
                        <tr>
                            <th>Declaration No</th>
                            <th>Shipment</th>
                            <th>Date</th>
                            <th>Value (USD)</th>
                            <th>Total Duties (ETB)</th>
                            <th>Status</th>
                            <th>Risk Channel</th>
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
        if ($('#customs-table').length > 0) {
            $('#customs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('logistics.customs.index') }}",
                columns: [
                    {data: 'declaration_no', name: 'declaration_no'},
                    {data: 'shipment_no', name: 'shipment_no'},
                    {data: 'declaration_date', name: 'declaration_date'},
                    {data: 'cif_value_usd', name: 'cif_value_usd'},
                    {data: 'total_duties', name: 'total_duties'},
                    {data: 'status', name: 'status'},
                    {data: 'risk_channel', name: 'risk_channel'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush
@endsection
