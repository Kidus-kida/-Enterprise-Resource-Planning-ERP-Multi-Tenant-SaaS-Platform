@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="reporting-dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="leave-card">
                    <div class="leave-card-header">
                        <h4><i class="fa fa-chart-bar"></i> {{ __('Reports & Analytics') }}</h4>
                    </div>
                    <div class="empty-state">
                        <i class="fa fa-chart-bar"></i>
                        <h5>{{ __('Leave Reports') }}</h5>
                        <p>{{ __('Analyze leave trends, balances, and utilization') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
