@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Reports & Analytics</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('logistics.dashboard') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                 <button class="btn btn-white"><i class="fa fa-calendar"></i> {{ now()->format('M Y') }}</button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-cubes"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $totalShipmentsYTD }}</h3>
                        <span>Total Shipments (YTD)</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-dollar"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ number_format($totalValueYTD / 1000000, 2) }}M</h3>
                        <span>Total Value (YTD)</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ $avgClearanceTime }}d</h3>
                        <span>Avg. Clearance Time</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
            <div class="card dash-widget">
                <div class="card-body">
                    <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                    <div class="dash-widget-info">
                        <h3>{{ number_format($dutiesPaidMTD / 1000000, 2) }}M</h3>
                        <span>Duties Paid (MTD)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Shipment Trends</h3>
                    <canvas id="shipmentTrendsChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Status Distribution</h3>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Duties by Category</h3>
                    <canvas id="dutiesChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Port Clearance Performance (Avg Days)</h3>
                    <canvas id="portChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Templates -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Report Templates</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary-light me-3">
                                        <i class="fa fa-file-text text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Monthly Shipment Summary</h5>
                                        <p class="mb-0 text-muted small">Overview of all shipments, status, and values</p>
                                    </div>
                                </div>
                                <a href="{{ route('logistics.reports.generate', ['type' => 'shipment_summary']) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-success-light me-3">
                                        <i class="fa fa-money text-success"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Duty Payment Report</h5>
                                        <p class="mb-0 text-muted small">Detailed breakdown of duties and taxes paid</p>
                                    </div>
                                </div>
                                <a href="{{ route('logistics.reports.generate', ['type' => 'duty_report']) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-warning-light me-3">
                                        <i class="fa fa-truck text-warning"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Container Tracking Report</h5>
                                        <p class="mb-0 text-muted small">Location and status of all containers</p>
                                    </div>
                                </div>
                                <a href="{{ route('logistics.reports.generate', ['type' => 'container_tracking']) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i></a>
                            </div>
                        </div>
                         <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-info-light me-3">
                                        <i class="fa fa-ship text-info"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Customs Clearance Analysis</h5>
                                        <p class="mb-0 text-muted small">Average clearance times and bottlenecks</p>
                                    </div>
                                </div>
                                <a href="{{ route('logistics.reports.generate', ['type' => 'customs_analysis']) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Data from Controller
        const months = @json($months);
        const shipmentCounts = @json($shipmentCounts);
        const shipmentValues = @json($shipmentValues);
        
        const statusData = @json($statusData);
        
        const dutiesCategories = @json($dutiesByCategory->pluck('section'));
        const dutiesValues = @json($dutiesByCategory->pluck('total'));
        
        const ports = @json(array_keys($portPerformance));
        const portValues = @json(array_values($portPerformance));

        // 1. Shipment Trends Chart (Area/Line)
        new Chart(document.getElementById('shipmentTrendsChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Shipments',
                    data: shipmentCounts,
                    borderColor: '#ff9b44',
                    backgroundColor: 'rgba(255, 155, 68, 0.1)',
                    yAxisID: 'y',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Value (ETB)',
                    data: shipmentValues,
                    borderColor: '#fc6075',
                    backgroundColor: 'rgba(252, 96, 117, 0.1)',
                    yAxisID: 'y1',
                    fill: true,
                    tension: 0.4,
                    hidden: true // Hide by default to keep clean
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { drawBorder: false }
                    },
                    y1: {
                        type: 'linear',
                        display: false, // Hidden axis
                        position: 'right',
                        grid: { drawOnChartArea: false },
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. Status Distribution (Doughnut)
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Released', 'In Transit', 'Pending', 'At Port'],
                datasets: [{
                    data: [statusData.released, statusData.in_transit, statusData.pending, statusData.at_port],
                    backgroundColor: [
                        '#55ce63', // Green
                        '#ffbc34', // Orange
                        '#ff9b44', // Theme Orange
                        '#00c5fb'  // Blue
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                 plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 3. Duties by Category (Bar)
        new Chart(document.getElementById('dutiesChart'), {
            type: 'bar',
            data: {
                labels: dutiesCategories,
                 datasets: [{
                    label: 'Duties Paid',
                    data: dutiesValues,
                    backgroundColor: '#fc6075',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                 scales: {
                    x: { grid: { display: false } },
                    y: { grid: { borderDash: [2, 4] } }
                }
            }
        });

        // 4. Port Performance (Horizontal Bar)
        new Chart(document.getElementById('portChart'), {
            type: 'bar',
            data: {
                labels: ports,
                datasets: [{
                    label: 'Avg Clearance Days',
                    data: portValues,
                    backgroundColor: '#ff9b44',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    x: { grid: { borderDash: [2, 4] } },
                    y: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endpush
@endsection
