@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="configuration-dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="leave-card">
                    <div class="leave-card-header">
                        <h4><i class="fa fa-cog"></i> {{ __('Configuration') }}</h4>
                    </div>
                    
                    <div class="config-grid mt-4">
                        <div class="row">
                            <div class="col-md-6 col-lg-3 mb-4">
                                <a href="{{ route('leave.config.time-off-types.index') }}" class="config-card-link">
                                    <div class="config-card">
                                        <div class="config-icon bg-primary">
                                            <i class="fa fa-list fa-2x"></i>
                                        </div>
                                        <h5>{{ __('Time Off Types') }}</h5>
                                        <p>{{ __('Define and manage different types of leave') }}</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 col-lg-3 mb-4">
                                <a href="{{ route('leave.config.accrual-plans.index') }}" class="config-card-link">
                                    <div class="config-card">
                                        <div class="config-icon bg-success">
                                            <i class="fa fa-calendar-plus fa-2x"></i>
                                        </div>
                                        <h5>{{ __('Accrual Plans') }}</h5>
                                        <p>{{ __('Configure automatic leave accrual rules') }}</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 col-lg-3 mb-4">
                                <a href="{{ route('leave.config.public-holidays.index') }}" class="config-card-link">
                                    <div class="config-card">
                                        <div class="config-icon bg-warning">
                                            <i class="fa fa-calendar-day fa-2x"></i>
                                        </div>
                                        <h5>{{ __('Public Holidays') }}</h5>
                                        <p>{{ __('Manage company-wide holidays') }}</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6 col-lg-3 mb-4">
                                <a href="{{ route('leave.config.mandatory-days.index') }}" class="config-card-link">
                                    <div class="config-card">
                                        <div class="config-icon bg-danger">
                                            <i class="fa fa-exclamation-circle fa-2x"></i>
                                        </div>
                                        <h5>{{ __('Mandatory Days') }}</h5>
                                        <p>{{ __('Define company shutdown periods') }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.config-card-link {
    text-decoration: none;
    color: inherit;
}

.config-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.config-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-color: #0d6efd;
}

.config-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #fff;
}

.config-card h5 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
}

.config-card p {
    font-size: 14px;
    color: #6c757d;
    margin: 0;
}
</style>
@endsection
