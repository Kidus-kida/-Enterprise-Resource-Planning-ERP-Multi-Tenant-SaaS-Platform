@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    {{-- Welcome & Actions --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h3 class="page-title">{{ __('My Time Off') }}</h3>
            <p class="text-muted">{{ __('Manage your leave requests and view your balances.') }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('leaverequests.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fa fa-plus me-2"></i>{{ __('New Request') }}
            </a>
        </div>
    </div>

    {{-- Leave Balances --}}
    <div class="row mb-4">
        @forelse($allocations as $allocation)
        <div class="col-md-4 col-xl-3 mb-3">
            <div class="leave-card h-100 p-3 border-start border-4" style="border-left-color: {{ $allocation->leaveType->color ?? '#0d6efd' }} !important;">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="text-muted mb-1">{{ $allocation->leaveType->type_name }}</h5>
                        <h2 class="mb-0 fw-bold">{{ $allocation->available_days }}</h2>
                        <small class="text-muted">{{ __('Days Available') }}</small>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-light text-dark mb-2">{{ $allocation->year }}</div>
                        <div class="small text-muted">{{ __('Used: ') }} {{ $allocation->used_days }}</div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 5px;">
                    @php
                        $total = $allocation->allocated_days > 0 ? $allocation->allocated_days : 1;
                        $percent = ($allocation->used_days / $total) * 100;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $allocation->leaveType->color ?? '#0d6efd' }};" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i> {{ __('No leave balances found for the current year.') }}
            </div>
        </div>
        @endforelse
    </div>

    <div class="row">
        {{-- Recent Requests --}}
        <div class="col-lg-8">
            <div class="leave-card">
                <div class="leave-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Recent Requests') }}</h5>
                    <a href="{{ route('leaverequests.myleaverequests') }}" class="small">{{ __('View All') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Dates') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                            <tr>
                                <td>
                                    <span class="badge" style="background-color: {{ $request->leaveType->color ?? '#secondary' }}; color: #fff;">
                                        {{ $request->leaveType->type_name ?? __('Leave') }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ \Carbon\Carbon::parse($request->leave_start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($request->leave_end_date)->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    @php
                                        // Simple duration calculation if not stored
                                        $start = \Carbon\Carbon::parse($request->leave_start_date);
                                        $end = \Carbon\Carbon::parse($request->leave_end_date);
                                        $days = $start->diffInDays($end) + 1; 
                                        if($request->half_day) $days = 0.5;
                                    @endphp
                                    {{ $days }} {{ __('days') }}
                                </td>
                                <td>
                                    @if($request->status == 'Approved')
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @elseif($request->status == 'Pending')
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    @elseif($request->status == 'Rejected')
                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $request->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    {{ __('No recent leave requests.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Upcoming Holidays --}}
        <div class="col-lg-4">
            <div class="leave-card">
                <div class="leave-card-header">
                    <h5 class="mb-0">{{ __('Upcoming Holidays') }}</h5>
                </div>
                <div class="p-3">
                    @forelse($holidays as $holiday)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom last-no-border">
                        <div class="calendar-icon me-3 text-center rounded p-2 bg-light text-primary border">
                            <div class="small fw-bold">{{ $holiday->startDate->format('M') }}</div>
                            <div class="h5 mb-0 fw-bold">{{ $holiday->startDate->format('d') }}</div>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $holiday->name }}</div>
                            <small class="text-muted">{{ $holiday->startDate->format('l') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        {{ __('No upcoming holidays.') }}
                    </div>
                    @endforelse
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('leave.config.public-holidays.calendar') }}" class="btn btn-sm btn-light w-100">{{ __('View Calendar') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.last-no-border:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
</style>
@endsection
