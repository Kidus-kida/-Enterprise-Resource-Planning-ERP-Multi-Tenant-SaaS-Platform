@extends('layouts.app')

@section('page-header')
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __('Accrual Plan') }}: {{ $accrualPlan->name }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('leave.config.accrual-plans.index') }}">{{ __('Accrual Plans') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('View') }}</li>
                </ul>
            </div>
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('leave.config.accrual-plans.edit', $accrualPlan->id) }}" class="btn btn-primary">
                    <i class="fa fa-pencil"></i> {{ __('Edit Plan') }}
                </a>
            </div>
        </div>
    </div>
@endsection

@section('page-content')
    <div class="content container-fluid">
        @include('leave.partials.nav')

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">+
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">{{ __('Configuration Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between p-3">
                                <span class="text-muted">{{ __('Gain Time') }}</span>
                                <span
                                    class="fw-bold text-capitalize">{{ str_replace('_', ' ', $accrualPlan->accrued_gain_time) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between p-3">
                                <span class="text-muted">{{ __('Transition Mode') }}</span>
                                <span
                                    class="fw-bold text-capitalize">{{ str_replace('_', ' ', $accrualPlan->transition_mode) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between p-3">
                                <span class="text-muted">{{ __('Carry-over Timing') }}</span>
                                <span
                                    class="fw-bold text-capitalize">{{ str_replace('_', ' ', $accrualPlan->carry_over_time) }}</span>
                            </li>
                            @if($accrualPlan->carry_over_time == 'other')
                                <li class="list-group-item d-flex justify-content-between p-3">
                                    <span class="text-muted">{{ __('Custom Date') }}</span>
                                    <span
                                        class="fw-bold">{{ date('F j', mktime(0, 0, 0, $accrualPlan->carry_over_month, $accrualPlan->carry_over_day)) }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between p-3">
                                <span class="text-muted">{{ __('Based on Worked Time') }}</span>
                                <span
                                    class="badge {{ $accrualPlan->is_based_on_worked_time ? 'bg-success' : 'bg-light text-muted' }}">
                                    {{ $accrualPlan->is_based_on_worked_time ? __('Yes') : __('No') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">{{ __('Accrual Milestones') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Tenure') }}</th>
                                        <th>{{ __('Accrual Rate') }}</th>
                                        <th>{{ __('Frequency') }}</th>
                                        <th>{{ __('Carry-over') }}</th>
                                        <th>{{ __('Caps') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accrualPlan->levels as $level)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if($level->start_count == 0)
                                                    <span class="text-primary fw-bold">{{ __('Immediate') }}</span>
                                                @else
                                                    {{ $level->start_count }} {{ ucfirst($level->start_type) }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $level->accrual_amount }}</span>
                                                <small class="text-muted text-uppercase">{{ $level->accrual_unit }}</small>
                                            </td>
                                            <td class="text-capitalize">{{ $level->accrual_frequency }}</td>
                                            <td>
                                                @if($level->action_with_unused_accruals == 'lost')
                                                    <span class="text-danger">{{ __('Lost') }}</span>
                                                @else
                                                    {{ __('Up to') }}
                                                    {{ $level->max_carryover ?? __('Unlimited') }}
                                                    @if($level->max_carryover)
                                                        <small
                                                            class="text-muted text-uppercase">{{ $level->max_carryover_unit }}</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($level->yearly_cap > 0)
                                                    <div class="small">Y: {{ $level->yearly_cap }}</div>
                                                @endif
                                                @if($level->cap_accrued_time > 0)
                                                    <div class="small">B: {{ $level->cap_accrued_time }}</div>
                                                @endif
                                                @if(!$level->yearly_cap && !$level->cap_accrued_time)
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection