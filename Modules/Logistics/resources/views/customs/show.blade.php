@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Declaration: {{ $declaration->declaration_no }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.customs.index') }}">Customs</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.customs.edit', $declaration->id) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Declaration Details <span class="badge badge-inverse-primary float-end">{{ ucfirst($declaration->status) }}</span></h3>
                     <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Shipment:</strong> <a href="{{ route('logistics.shipments.show', $declaration->shipment_id) }}">{{ $declaration->shipment->shipment_no }}</a>
                        </li>
                        <li class="list-group-item">
                            <strong>Declaration Date:</strong> {{ $declaration->declaration_date ? $declaration->declaration_date->format('d M Y') : '-' }}
                        </li>
                        <li class="list-group-item">
                            <strong>HS Code:</strong> {{ $declaration->hsCode->code }}
                        </li>
                        <li class="list-group-item">
                            <strong>Risk Channel:</strong> 
                            <span class="badge bg-{{ $declaration->risk_channel == 'green' ? 'success' : ($declaration->risk_channel == 'yellow' ? 'warning' : 'danger') }}">{{ ucfirst($declaration->risk_channel) }}</span>
                        </li>
                         <li class="list-group-item">
                            <strong>Exchange Rate:</strong> {{ $declaration->exchange_rate }}
                        </li>
                        <li class="list-group-item">
                            <strong>CIF Value:</strong> USD {{ number_format((float)$declaration->cif_value_usd, 2) }} / ETB {{ number_format((float)$declaration->cif_value_etb, 2) }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
         <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Duties & Taxes</h3>
                     <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Import Duty
                            <span>ETB {{ number_format((float)$declaration->import_duty, 2) }}</span>
                        </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            Excise Tax
                            <span>ETB {{ number_format((float)$declaration->excise, 2) }}</span>
                        </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            VAT
                            <span>ETB {{ number_format((float)$declaration->vat, 2) }}</span>
                        </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            Surtax
                            <span>ETB {{ number_format((float)$declaration->surtax, 2) }}</span>
                        </li>
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            Withholding Tax
                            <span>ETB {{ number_format((float)$declaration->withholding, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Service Fee
                            <span>ETB {{ number_format((float)$declaration->customs_service_fee, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                            <strong>Total Payable</strong>
                            <strong>ETB {{ number_format((float)$declaration->total_duties, 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
