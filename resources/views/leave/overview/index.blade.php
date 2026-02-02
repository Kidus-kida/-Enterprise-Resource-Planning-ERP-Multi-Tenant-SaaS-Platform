@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="overview-dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="leave-card">
                    <div class="leave-card-header">
                        <h4><i class="fa fa-users"></i> {{ __('Team Overview') }}</h4>
                    </div>
                    <div class="empty-state">
                        <i class="fa fa-users"></i>
                        <h5>{{ __('Team Leave Overview') }}</h5>
                        <p>{{ __('View your team members on leave and pending approval requests') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
