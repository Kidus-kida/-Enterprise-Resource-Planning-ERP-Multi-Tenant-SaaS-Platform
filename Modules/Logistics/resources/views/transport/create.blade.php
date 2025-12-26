@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Schedule New Trip</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.transport.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Trip No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="trip_no" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Container <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="container_id" required>
                                        <option value="">Select Container</option>
                                        @foreach($containers as $container)
                                            <option value="{{ $container->id }}">{{ $container->container_no }} ({{ $container->shipment ? $container->shipment->shipment_no : 'No Shipment' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Origin <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="origin" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Destination <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="destination" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Driver Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="driver_name" required>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Driver Phone </label>
                                    <input class="form-control" type="text" name="driver_phone">
                                </div>
                            </div>
                        </div>
                        
                         <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Vehicle Plate No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="vehicle_plate" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Estimated Distance (KM)</label>
                                    <input class="form-control" type="number" name="distance_km">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Departure Date</label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="departed_at">
                                    </div>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Estimated Arrival (ETA)</label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="eta">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Schedule Trip</button>
                            <a href="{{ route('logistics.transport.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
