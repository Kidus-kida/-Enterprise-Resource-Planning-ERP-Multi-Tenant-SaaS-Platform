@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Edit Customs Declaration</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.customs.update', $declaration->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Shipment <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="shipment_id" required>
                                        <option value="">Select Shipment</option>
                                        @foreach($shipments as $shipment)
                                            <option value="{{ $shipment->id }}" {{ $declaration->shipment_id == $shipment->id ? 'selected' : '' }}>{{ $shipment->shipment_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Declaration No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="declaration_no" value="{{ $declaration->declaration_no }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Declaration Date <span class="text-danger">*</span></label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="declaration_date" value="{{ $declaration->declaration_date ? $declaration->declaration_date->format('Y-m-d') : '' }}" required>
                                    </div>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>HS Code <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="hs_code_id" required>
                                        <option value="">Select HS Code</option>
                                        @foreach($hsCodes as $code)
                                            <option value="{{ $code->id }}" {{ $declaration->hs_code_id == $code->id ? 'selected' : '' }}>{{ $code->code }} - {{ Str::limit($code->description, 30) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>CIF Value (USD) <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.01" name="cif_value_usd" value="{{ $declaration->cif_value_usd }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Exchange Rate <span class="text-danger">*</span></label>
                                    <input class="form-control" type="number" step="0.0001" name="exchange_rate" value="{{ $declaration->exchange_rate }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Risk Channel</label>
                                    <select class="select form-control" name="risk_channel">
                                        @foreach(['green', 'yellow', 'red', 'blue'] as $channel)
                                            <option value="{{ $channel }}" {{ $declaration->risk_channel == $channel ? 'selected' : '' }}>{{ ucfirst($channel) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="select form-control" name="status">
                                        @foreach(['draft', 'submitted', 'assessed', 'paid', 'released'] as $status)
                                            <option value="{{ $status }}" {{ $declaration->status == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                         <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Total Duties (ETB)</label>
                                    <input class="form-control" type="number" step="0.01" name="total_duties" value="{{ $declaration->total_duties }}">
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Update Declaration</button>
                            <a href="{{ route('logistics.customs.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
