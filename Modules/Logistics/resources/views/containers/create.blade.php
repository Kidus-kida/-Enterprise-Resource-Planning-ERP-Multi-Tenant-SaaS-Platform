@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Add Container</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.containers.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                         <div class="form-group">
                            <label>Shipment <span class="text-danger">*</span></label>
                            <select class="select form-control" name="shipment_id" required>
                                <option value="">Select Shipment</option>
                                @foreach($shipments as $shipment)
                                    <option value="{{ $shipment->id }}">{{ $shipment->shipment_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Container No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="container_no" required>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Seal No</label>
                                    <input class="form-control" type="text" name="seal_no">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Shipping Line <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="shipping_line" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Size <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="size">
                                        <option value="20ft">20ft</option>
                                        <option value="40ft">40ft</option>
                                        <option value="45ft">45ft</option>
                                        <option value="LCL">LCL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="type">
                                        <option value="dry">Dry</option>
                                        <option value="reefer">Reefer</option>
                                        <option value="flat_rack">Flat Rack</option>
                                        <option value="open_top">Open Top</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current Status</label>
                            <select class="select form-control" name="status">
                                <option value="pending">Pending</option>
                                <option value="vessel_departed">Vessel Departed</option>
                                <option value="at_djibouti">At Djibouti</option>
                                <option value="in_transit">In Transit</option>
                                <option value="at_dry_port">At Dry Port</option>
                                <option value="released">Released</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                         <!-- Extra fields for tracking -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input class="form-control" type="text" name="location" placeholder="Current Location">
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Demurrage Days</label>
                                    <input class="form-control" type="number" name="demurrage_days" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Submit</button>
                            <a href="{{ route('logistics.containers.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
