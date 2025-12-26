@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Edit Shipment</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.shipments.index') }}">Shipments</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('logistics.shipments.update', $shipment->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="row">
                            <h4 class="card-title">Shipment Details</h4>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipment No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="shipment_no" value="{{ old('shipment_no', $shipment->shipment_no) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>PO Reference</label>
                                    <input type="text" class="form-control" name="po_reference" value="{{ old('po_reference', $shipment->po_reference) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vendor" value="{{ old('vendor', $shipment->vendor) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="vendor_country" value="{{ old('vendor_country', $shipment->vendor_country) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Incoterms <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="incoterms">
                                        @foreach(['CIF', 'FOB', 'EXW', 'DDP', 'CFR', 'DAP'] as $term)
                                            <option value="{{ $term }}" {{ old('incoterms', $shipment->incoterms) == $term ? 'selected' : '' }}>{{ $term }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transport Mode <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="transport_mode">
                                        @foreach(['sea', 'air', 'rail', 'truck'] as $mode)
                                            <option value="{{ $mode }}" {{ old('transport_mode', $shipment->transport_mode) == $mode ? 'selected' : '' }}>{{ ucfirst($mode) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Port of Loading <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="port_of_loading" value="{{ old('port_of_loading', $shipment->port_of_loading) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Port of Discharge <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="port_of_discharge" value="{{ old('port_of_discharge', $shipment->port_of_discharge) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expected Arrival <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="expected_arrival" value="{{ old('expected_arrival', $shipment->expected_arrival ? $shipment->expected_arrival->format('Y-m-d') : '') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipment Value (ETB)</label>
                                    <input type="number" step="0.01" class="form-control" name="value_etb" value="{{ old('value_etb', $shipment->value_etb) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Dry Port</label>
                                    <select class="select form-control" name="dry_port_id">
                                        <option value="">Select Dry Port</option>
                                        @foreach($dryPorts as $port)
                                            <option value="{{ $port->id }}" {{ old('dry_port_id', $shipment->dry_port_id) == $port->id ? 'selected' : '' }}>{{ $port->name }} ({{ $port->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="select form-control" name="status">
                                        @foreach(['pending', 'vessel_departed', 'at_djibouti', 'in_transit', 'customs_clearance', 'released', 'delivered'] as $status)
                                            <option value="{{ $status }}" {{ old('status', $shipment->status) == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
