@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-calendar-plus"></i> {{ __('Accrual Plans') }}</h4>
            <a href="{{ route('leave.config.accrual-plans.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> {{ __('Create New') }}
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Leave Type') }}</th>
                        <th>{{ __('Accrual Rate') }}</th>
                        <th>{{ __('Max Accrual') }}</th>
                        <th>{{ __('Carryover') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accrualPlans as $plan)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold">{{ $plan->name }}</div>
                            <small class="text-muted">{{ $plan->description }}</small>
                        </td>
                        <td>
                            @if($plan->leaveType)
                                <span class="badge" style="background-color: {{ $plan->leaveType->color ?? '#6c757d' }}; color: #fff;">
                                    {{ $plan->leaveType->type_name }}
                                </span>
                            @else
                                <span class="text-muted">{{ __('N/A') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $plan->accrual_rate }} {{ __('days') }}</div>
                            <small class="text-muted text-capitalize">{{ str_replace('_', ' ', $plan->accrual_frequency) }}</small>
                        </td>
                        <td>
                            @if($plan->max_accrual_days)
                                {{ $plan->max_accrual_days }} {{ __('days') }}
                            @else
                                <span class="badge bg-light text-dark">{{ __('Unlimited') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan->allow_carryover)
                                <span class="badge bg-success">{{ __('Allowed') }}</span>
                                @if($plan->max_carryover_days)
                                    <small>({{ $plan->max_carryover_days }} max)</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">{{ __('Disabled') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($plan->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('leave.config.accrual-plans.edit', $plan->id) }}"><i class="fa fa-pencil m-r-5"></i> {{ __('Edit') }}</a>
                                    <form action="{{ route('leave.config.accrual-plans.destroy', $plan->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item delete-btn" onclick="return confirm('Are you sure?')"><i class="fa fa-trash-o m-r-5"></i> {{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fa fa-calendar-plus mb-3"></i>
                                <h5>{{ __('No Accrual Plans Found') }}</h5>
                                <p>{{ __('Create accrual plans to automate leave allocation regarding rates.') }}</p>
                                <a href="{{ route('leave.config.accrual-plans.create') }}" class="btn btn-primary mt-2">
                                    {{ __('Create New') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
