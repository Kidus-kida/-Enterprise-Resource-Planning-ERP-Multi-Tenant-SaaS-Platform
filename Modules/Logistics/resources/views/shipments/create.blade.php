@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Create Shipment</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.shipments.index') }}">Shipments</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('logistics.shipments.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <h4 class="card-title">Shipment Details</h4>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipment No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="shipment_no" value="{{ old('shipment_no') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PO Reference</label>
                                    <input type="text" class="form-control" name="po_reference" value="{{ old('po_reference') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vendor" value="{{ old('vendor') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vendor_country" value="{{ old('vendor_country') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incoterms <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="incoterms">
                                        <option value="CIF">CIF</option>
                                        <option value="FOB">FOB</option>
                                        <option value="EXW">EXW</option>
                                        <option value="DDP">DDP</option>
                                        <option value="CFR">CFR</option>
                                        <option value="DAP">DAP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transport Mode <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="transport_mode">
                                        <option value="sea">Sea</option>
                                        <option value="air">Air</option>
                                        <option value="rail">Rail</option>
                                        <option value="truck">Truck</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Port of Loading <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="port_of_loading" value="{{ old('port_of_loading') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Port of Discharge <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="port_of_discharge" value="{{ old('port_of_discharge', 'Djibouti') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expected Arrival <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="expected_arrival" value="{{ old('expected_arrival') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipment Value (ETB)</label>
                                    <input type="number" step="0.01" class="form-control" name="value_etb" value="{{ old('value_etb') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Dry Port</label>
                                    <select class="select form-control" name="dry_port_id">
                                        <option value="">Select Dry Port</option>
                                        @foreach($dryPorts as $port)
                                            <option value="{{ $port->id }}">{{ $port->name }} ({{ $port->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
