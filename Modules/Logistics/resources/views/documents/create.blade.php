@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Upload New Document</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.documents.store') }}" method="POST" enctype="multipart/form-data">
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
                                            <option value="{{ $shipment->id }}">{{ $shipment->shipment_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Document Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="name" required>
                                </div>
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Document Type <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="type" required>
                                        <option value="invoice">Commercial Invoice</option>
                                        <option value="packing_list">Packing List</option>
                                        <option value="bill_of_lading">Bill of Lading</option>
                                        <option value="bank_permit">Bank Permit</option>
                                        <option value="insurance">Insurance Policy</option>
                                        <option value="customs_declaration">Customs Declaration</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>File <span class="text-danger">*</span></label>
                                    <input class="form-control" type="file" name="file" required>
                                    <small class="form-text text-muted">Max 10MB. Allowed: pdf, doc, docx, jpg, png</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Upload Document</button>
                            <a href="{{ route('logistics.documents.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
