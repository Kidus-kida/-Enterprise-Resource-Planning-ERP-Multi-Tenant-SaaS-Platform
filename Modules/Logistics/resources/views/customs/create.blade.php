@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">New Customs Declaration</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.customs.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Shipment <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="shipment_id" required>
                                        <option value="">Select Shipment</option>
                                        @foreach($shipments as $shipment)
                                            <option value="{{ $shipment->id }}" {{ (isset($selectedShipment) && $selectedShipment->id == $shipment->id) ? 'selected' : '' }}>{{ $shipment->shipment_no }}</option>
                                        @endforeach
                                        @if(isset($selectedShipment) && !$shipments->contains($selectedShipment))
                                             <option value="{{ $selectedShipment->id }}" selected>{{ $selectedShipment->shipment_no }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Declaration No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="declaration_no" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Declaration Date <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="declaration_date" required>
                                    </div>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>HS Code <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="hs_code_id" required>
                                        <option value="">Select HS Code</option>
                                        @foreach($hsCodes as $code)
                                            <option value="{{ $code->id }}">{{ $code->code }} - {{ Str::limit($code->description, 30) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>CIF Value (USD) <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.01" name="cif_value_usd" id="cif_usd" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Exchange Rate <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.0001" name="exchange_rate" id="exchange_rate" value="120.0000" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Risk Channel</label>
                                    <select class="select form-control" name="risk_channel">
                                        <option value="green">Green</option>
                                        <option value="yellow">Yellow</option>
                                        <option value="red">Red</option>
                                        <option value="blue">Blue</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="select form-control" name="status">
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="assessed">Assessed</option>
                                        <option value="paid">Paid</option>
                                        <option value="released">Released</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Create Declaration</button>
                            <a href="{{ route('logistics.customs.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
