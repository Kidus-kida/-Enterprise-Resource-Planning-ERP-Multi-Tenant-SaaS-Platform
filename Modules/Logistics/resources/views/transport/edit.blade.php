@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Edit Trip Details</h3>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('logistics.transport.update', $trip->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Trip No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="trip_no" value="{{ $trip->trip_no }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Container <span class="text-danger">*</span></label>
                                    <select class="select form-control" name="container_id" required>
                                        <option value="">Select Container</option>
                                        @foreach($containers as $container)
                                            <option value="{{ $container->id }}" {{ $trip->container_id == $container->id ? 'selected' : '' }}>{{ $container->container_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                         <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Origin <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="origin" value="{{ $trip->origin }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Destination <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="destination" value="{{ $trip->destination }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Driver Name <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="driver_name" value="{{ $trip->driver_name }}" required>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Driver Phone </label>
                                    <input class="form-control" type="text" name="driver_phone" value="{{ $trip->driver_phone }}">
                                </div>
                            </div>
                        </div>
                        
                         <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Vehicle Plate No <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" name="vehicle_plate" value="{{ $trip->vehicle_plate }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="select form-control" name="status">
                                        @foreach(['scheduled', 'loading', 'in_transit', 'completed', 'delayed', 'cancelled'] as $status)
                                            <option value="{{ $status }}" {{ $trip->status == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Departure Date</label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="departed_at" value="{{ $trip->departed_at ? $trip->departed_at->format('Y-m-d H:i') : '' }}">
                                    </div>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Estimated Arrival (ETA)</label>
                                    <div class="cal-icon">
                                        <input class="form-control datetimepicker" type="text" name="eta" value="{{ $trip->eta ? $trip->eta->format('Y-m-d H:i') : '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Progress (%)</label>
                            <input type="range" class="form-range form-control" name="progress" min="0" max="100" value="{{ $trip->progress }}">
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Update Trip</button>
                            <a href="{{ route('logistics.transport.index') }}" class="btn btn-secondary submit-btn">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
