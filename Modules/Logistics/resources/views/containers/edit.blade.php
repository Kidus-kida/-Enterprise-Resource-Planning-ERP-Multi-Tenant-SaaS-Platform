@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Edit Container</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.containers.update', $container->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                         <div class="form-group">
                            <label>Shipment <span class="text-danger">*</span></label>
                            <select class="select form-control" name="shipment_id" required>
                                <option value="">Select Shipment</option>
                                @foreach($shipments as $shipment)
                                    <option value="{{ $shipment->id }}" {{ $container->shipment_id == $shipment->id ? 'selected' : '' }}>{{ $shipment->shipment_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Container No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="container_no" value="{{ $container->container_no }}" required>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Seal No</label>
                                    <input class="form-control" type="text" name="seal_no" value="{{ $container->seal_no }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Shipping Line <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="shipping_line" value="{{ $container->shipping_line }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Size <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="size">
                                        <option value="20ft" {{ $container->size == '20ft' ? 'selected' : '' }}>20ft</option>
                                        <option value="40ft" {{ $container->size == '40ft' ? 'selected' : '' }}>40ft</option>
                                        <option value="45ft" {{ $container->size == '45ft' ? 'selected' : '' }}>45ft</option>
                                        <option value="LCL" {{ $container->size == 'LCL' ? 'selected' : '' }}>LCL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="type">
                                        <option value="dry" {{ $container->type == 'dry' ? 'selected' : '' }}>Dry</option>
                                        <option value="reefer" {{ $container->type == 'reefer' ? 'selected' : '' }}>Reefer</option>
                                        <option value="flat_rack" {{ $container->type == 'flat_rack' ? 'selected' : '' }}>Flat Rack</option>
                                        <option value="open_top" {{ $container->type == 'open_top' ? 'selected' : '' }}>Open Top</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current Status</label>
                            <select class="select form-control" name="status">
                                @foreach(['pending', 'vessel_departed', 'at_djibouti', 'in_transit', 'at_dry_port', 'released', 'delivered'] as $status)
                                    <option value="{{ $status }}" {{ $container->status == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                         <!-- Extra fields for tracking -->
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Location</label>
                                    <input class="form-control" type="text" name="location" value="{{ $container->location }}" placeholder="Current Location">
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Demurrage Days</label>
                                    <input class="form-control" type="number" name="demurrage_days" value="{{ $container->demurrage_days }}">
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Update</button>
                            <a href="{{ route('logistics.containers.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
