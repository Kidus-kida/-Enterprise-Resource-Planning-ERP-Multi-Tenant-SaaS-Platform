@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="management-dashboard">
        <div class="row">
            <div class="col-md-6 mb-4">
                <a href="{{ route('leaverequests.index') }}" class="config-card-link">
                    <div class="leave-card h-100 p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ __('Leave Requests') }}</h5>
                                <p class="text-muted mb-0 small">{{ __('Review and approve employee requests') }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 mb-4">
                <a href="{{ route('leave.management.allocations.index') }}" class="config-card-link">
                    <div class="leave-card h-100 p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                <i class="fa fa-chart-pie fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ __('Allocations') }}</h5>
                                <p class="text-muted mb-0 small">{{ __('Manage employee leave balances') }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.config-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}
.config-card-link:hover .leave-card {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.2s;
}
</style>
@endsection
