@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Dashboard</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Overview</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row">
        <!-- Total Shipments -->
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-ship"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalShipments }}</h3>
                        <span>Total Shipments</span>
                        <div class="text-muted small mt-1">This year</div>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- In Transit -->
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-anchor"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $inTransit }}</h3>
                        <span>In Transit</span>
                        <div class="text-muted small mt-1">{{ $atDjibouti }} At Djibouti</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Clearance -->
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-file-text"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $pendingClearance }}</h3>
                        <span>Pending Clearance</span>
                         <div class="text-muted small mt-1">Awaiting Customs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Released -->
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $released }}</h3>
                        <span>Released</span>
                        <div class="text-muted small mt-1">Ready for Delivery</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Secondary Stats -->
    <div class="row">
         <div class="col-md-4">
             <div class="card flex-fill">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                         <div>
                             <h4 class="mb-1">{{ $avgClearanceTime }} days</h4>
                             <p class="mb-0 text-muted">Avg. Clearance Time</p>
                         </div>
                         <div class="avatar avatar-md bg-light">
                             <i class="fa fa-clock-o text-purple"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-md-4">
             <div class="card flex-fill">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                         <div>
                             <h4 class="mb-1">{{ number_format($totalDutiesPaidMTD / 1000000, 2) }}M ETB</h4>
                             <p class="mb-0 text-muted">Duties Paid (MTD)</p>
                         </div>
                         <div class="avatar avatar-md bg-light">
                             <i class="fa fa-money text-success"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-md-4">
             <div class="card flex-fill">
                 <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center">
                         <div>
                             <h4 class="mb-1">{{ $demurrageAtRisk }} Containers</h4>
                             <p class="mb-0 text-muted">Demurrage Risk</p>
                         </div>
                         <div class="avatar avatar-md bg-light">
                             <i class="fa fa-exclamation-triangle text-warning"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
    </div>


    <!-- Recent Shipments -->
    <div class="row">
        <div class="col-md-12 d-flex">
            <div class="card card-table flex-fill">
                <div class="card-header pb-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title mb-0">Recent Shipments</h3>
                            <p class="text-muted small">Track your latest import shipments</p>
                        </div>
                        <div class="col-auto float-end ms-auto">
                            <a href="{{ route('logistics.shipments.index') }}" class="btn btn-outline-secondary btn-sm">View All</a>
                            <a href="{{ route('logistics.shipments.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Shipment</a>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                     <div class="row">
                         @forelse($recentShipments as $shipment)
                             <div class="col-md-6 col-lg-3 d-flex">
                                 <div class="card flex-fill border shadow-sm hover-shadow transition-all" style="border-radius: 0.75rem;">
                                     <div class="card-body p-4">
                                         <!-- Header -->
                                         <div class="d-flex justify-content-between align-items-start mb-4">
                                             <div class="d-flex align-items-center gap-3">
                                                 <!-- Icon Box -->
                                                 <div class="rounded-lg d-flex align-items-center justify-content-center" 
                                                      style="width: 40px; height: 40px; background-color: rgba(0, 197, 251, 0.1); color: #00c5fb;">
                                                     @if($shipment->transport_mode == 'air') <i class="fa fa-cube" style="font-size: 1.25rem;"></i>
                                                     @elseif($shipment->transport_mode == 'sea') <i class="fa fa-ship" style="font-size: 1.25rem;"></i>
                                                     @elseif($shipment->transport_mode == 'truck') <i class="fa fa-truck" style="font-size: 1.25rem;"></i>
                                                     @else <i class="fa fa-cube" style="font-size: 1.25rem;"></i>
                                                     @endif
                                                 </div>
                                                 <!-- Title & Subtitle -->
                                                 <div class="ms-3">
                                                     <h5 class="mb-0 font-weight-bold">
                                                         <a href="{{ route('logistics.shipments.show', $shipment->id) }}" class="text-dark">{{ $shipment->shipment_no }}</a>
                                                     </h5>
                                                     <small class="text-muted">{{ $shipment->po_reference ?? 'No Reference' }}</small>
                                                 </div>
                                             </div>
                                             <!-- Status Badge -->
                                             <span class="badge rounded-pill bg-inverse-{{ $shipment->status == 'released' ? 'success' : ($shipment->status == 'pending' ? 'warning' : 'info') }}" 
                                                   style="font-size: 0.75rem; padding: 0.5em 0.8em;">
                                                 {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                             </span>
                                         </div>

                                         <div class="d-flex flex-column gap-3">
                                             <!-- Vendor Row -->
                                             <div class="d-flex justify-content-between align-items-center text-sm">
                                                 <span class="text-muted">Vendor</span>
                                                 <span class="font-weight-500 text-dark">{{ Str::limit($shipment->vendor, 20) }}</span>
                                             </div>

                                             <!-- Route Row -->
                                             <div class="d-flex align-items-center text-sm gap-2">
                                                 <i class="fa fa-map-marker text-muted" style="font-size: 14px;"></i>
                                                 <span class="text-muted">{{ $shipment->port_of_loading }}</span>
                                                 <i class="fa fa-arrow-right text-muted mx-2" style="font-size: 12px;"></i>
                                                 <span class="text-dark font-weight-500">
                                                     {{ $shipment->port_of_discharge }}
                                                 </span>
                                             </div>

                                             <!-- Footer (Border Top) -->
                                             <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-2">
                                                 <div class="d-flex align-items-center gap-3">
                                                     <span class="text-muted small d-flex align-items-center">
                                                         <i class="fa fa-cube me-1"></i> {{ $shipment->containers->count() }} containers
                                                     </span>
                                                     <span class="text-muted small d-flex align-items-center ms-3">
                                                         <i class="fa fa-file-text-o me-1"></i> {{ $shipment->documents->count() }} docs
                                                     </span>
                                                 </div>
                                                 <span class="text-muted small d-flex align-items-center">
                                                     <i class="fa fa-calendar me-1"></i> ETA: {{ $shipment->expected_arrival ? $shipment->expected_arrival->format('M d') : 'N/A' }}
                                                 </span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         @empty
                            <div class="col-12 text-center py-4">
                                <p class="text-muted">No recent shipments found.</p>
                            </div>
                         @endforelse
                     </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
     <div class="row">
         <div class="col-md-4">
             <div class="card">
                 <div class="card-body p-4">
                     <div class="d-flex align-items-center mb-3">
                         <div class="avatar avatar-md bg-primary-light me-3">
                             <i class="fa fa-file-text text-primary"></i>
                         </div>
                         <div>
                             <h4 class="mb-0">Customs Declarations</h4>
                             <p class="text-muted mb-0 small">{{ $pendingClearance }} pending review</p>
                         </div>
                     </div>
                     <a href="{{ route('logistics.customs.index') }}" class="btn btn-outline-primary w-100 btn-sm">View Declarations</a>
                 </div>
             </div>
         </div>
         <div class="col-md-4">
             <div class="card">
                 <div class="card-body p-4">
                     <div class="d-flex align-items-center mb-3">
                         <div class="avatar avatar-md bg-warning-light me-3">
                             <i class="fa fa-exclamation-triangle text-warning"></i>
                         </div>
                         <div>
                             <h4 class="mb-0">Documents & Reports</h4>
                             <p class="text-muted mb-0 small">Manage files</p>
                         </div>
                     </div>
                     <a href="{{ route('logistics.documents.index') }}" class="btn btn-outline-warning w-100 btn-sm">Review Documents</a>
                 </div>
             </div>
         </div>
         <div class="col-md-4">
             <div class="card">
                 <div class="card-body p-4">
                     <div class="d-flex align-items-center mb-3">
                         <div class="avatar avatar-md bg-success-light me-3">
                             <i class="fa fa-bar-chart text-success"></i>
                         </div>
                         <div>
                             <h4 class="mb-0">Monthly Reports</h4>
                             <p class="text-muted mb-0 small">Generate insights</p>
                         </div>
                     </div>
                     <a href="{{ route('logistics.reports.index') }}" class="btn btn-outline-success w-100 btn-sm">View Reports</a>
                 </div>
             </div>
         </div>
     </div>
</div>
@endsection
