@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Trip Details: {{ $trip->trip_no }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('logistics.transport.index') }}">Transport</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('logistics.transport.edit', $trip->id) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
             <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Route Map <small class="text-muted">(Simulated)</small></h3>
                    <div style="background: #f0f0f0; height: 300px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                        <div class="text-center">
                            <i class="fa fa-map-marker fa-3x text-danger"></i>
                            <h4 class="mt-2 text-muted">Map API Integration Required</h4>
                            <p>{{ $trip->origin }} <i class="fa fa-arrow-right"></i> {{ $trip->destination }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
             <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Status Timeline</h3>
                    <div class="experience-box">
                        <ul class="experience-list">
                            <li>
                                <div class="experience-user">
                                    <div class="before-circle"></div>
                                </div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#/" class="name">Scheduled</a>
                                        <span class="time">{{ $trip->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                </div>
                            </li>
                            @if($trip->departed_at)
                             <li>
                                <div class="experience-user">
                                    <div class="before-circle"></div>
                                </div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#/" class="name">Departed Origin</a>
                                        <span class="time">{{ $trip->departed_at->format('d M Y H:i') }}</span>
                                    </div>
                                </div>
                            </li>
                            @endif
                             <li>
                                <div class="experience-user">
                                    <div class="before-circle"></div>
                                </div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#/" class="name">In Transit</a>
                                        <span class="time">Progress: {{ $trip->progress }}%</span>
                                        <div class="progress progress-xs mb-0 mt-2">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $trip->progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                              @if($trip->eta)
                             <li>
                                <div class="experience-user">
                                    <div class="before-circle"></div>
                                </div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#/" class="name">Estimated Arrival</a>
                                        <span class="time">{{ $trip->eta->format('d M Y H:i') }}</span>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
             <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Driver & Vehicle</h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Driver:</strong> {{ $trip->driver_name }}
                        </li>
                        <li class="list-group-item">
                            <strong>Phone:</strong> {{ $trip->driver_phone ?? 'N/A' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Vehicle:</strong> {{ $trip->vehicle_plate }}
                        </li>
                    </ul>
                </div>
            </div>
            
             <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Container Info</h3>
                     <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Container No:</strong> <a href="{{ route('logistics.containers.edit', $trip->container_id) }}">{{ $trip->container->container_no }}</a>
                        </li>
                        <li class="list-group-item">
                            <strong>Shipment:</strong> {{ $trip->container->shipment ? $trip->container->shipment->shipment_no : 'N/A' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Type:</strong> {{ ucfirst($trip->container->type) }} {{ $trip->container->size }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
