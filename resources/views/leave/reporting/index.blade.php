@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="reporting-dashboard">
        {{-- Stat Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">{{ __('Total Requests') }}</h6>
                                <h3 class="mb-0 text-white">{{ $stats['total_requests'] }}</h3>
                            </div>
                            <i class="fa fa-file-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">{{ __('Approved') }}</h6>
                                <h3 class="mb-0 text-white">{{ $stats['approved'] }}</h3>
                            </div>
                            <i class="fa fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">{{ __('Pending') }}</h6>
                                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                            </div>
                            <i class="fa fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">{{ __('Rejected') }}</h6>
                                <h3 class="mb-0 text-white">{{ $stats['rejected'] }}</h3>
                            </div>
                            <i class="fa fa-times-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Leave Utilization --}}
            <div class="col-md-6">
                <div class="leave-card h-100">
                    <div class="leave-card-header">
                        <h5 class="mb-0">{{ __('Leave Type Utilization') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Leave Type') }}</th>
                                    <th class="text-end">{{ __('Requests') }}</th>
                                    <th class="text-end">{{ __('Utilization %') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($utilization as $type)
                                @php
                                    $percent = ($stats['total_requests'] > 0) 
                                        ? round(($type->days_used / $stats['total_requests']) * 100, 1) 
                                        : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge me-2" style="background-color: {{ $type->color ?? '#6c757d' }};"> </span>
                                        {{ $type->type_name }}
                                    </td>
                                    <td class="text-end">{{ $type->days_used }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="me-2">{{ $percent }}%</span>
                                            <div class="progress" style="width: 50px; height: 4px;">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $type->color ?? '#6c757d' }};"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="col-md-6">
                <div class="leave-card h-100">
                    <div class="leave-card-header">
                        <h5 class="mb-0">{{ __('Recent Activity Log') }}</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($recent_activity as $activity)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $activity->user->name ?? 'Unknown' }}</h6>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1 text-muted small">
                                Requested {{ $activity->total_days ?? 1 }} days of 
                                <strong style="color: {{ $activity->leaveType->color ?? 'black' }}">{{ $activity->leaveType->type_name }}</strong>.
                            </p>
                            <span class="badge bg-light text-dark border">{{ $activity->status }}</span>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            {{ __('No recent activity found.') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
