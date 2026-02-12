@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        @include('leave.partials.nav')

        <div class="leave-card">
            <div class="leave-card-header d-flex justify-content-between align-items-center">
                <h4><i class="fa fa-calendar-alt"></i> {{ __('Accrual Plan') }}</h4>
                <a href="{{ route('leave.config.accrual-plans.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> {{ __('Create New') }}
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-nowrap custom-table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('Plan Name') }}</th>
                            <th>{{ __('Milestones') }}</th>
                            <th>{{ __('Worked Time') }}</th>
                            <th>{{ __('Gain Time') }}</th>
                            <th>{{ __('Carry-over') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accrualPlans as $plan)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs bg-primary-light me-2">
                                            <i class="fa fa-calendar-check text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $plan->name }}</h6>
                                            <small class="text-muted">{{ $plan->levels->count() }}
                                                {{ __('level(s)') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-primary-light text-primary fw-bold">{{ $plan->levels->count() }}</span>
                                </td>
                                <td>
                                    @if($plan->is_based_on_worked_time)
                                        <span class="badge bg-success-light text-success">{{ __('Prorated') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">{{ __('Fixed') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="text-capitalize small bg-light p-1 px-2 rounded">{{ str_replace('_', ' ', $plan->accrued_gain_time ?? 'start') }}</span>
                                </td>
                                <td>
                                    <div class="small">
                                        <span
                                            class="fw-bold text-capitalize">{{ str_replace('_', ' ', $plan->carry_over_time) }}</span>
                                        @if($plan->carry_over_time == 'other')
                                            <div class="text-muted">
                                                {{ date('M j', mktime(0, 0, 0, $plan->carry_over_month, $plan->carry_over_day)) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item"
                                                href="{{ route('leave.config.accrual-plans.show', $plan->id) }}"><i
                                                    class="fa fa-eye m-r-5"></i> {{ __('View') }}</a>
                                            <a class="dropdown-item"
                                                href="{{ route('leave.config.accrual-plans.edit', $plan->id) }}"><i
                                                    class="fa fa-pencil m-r-5"></i> {{ __('Edit') }}</a>
                                            <form action="{{ route('leave.config.accrual-plans.destroy', $plan->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item"
                                                    onclick="return confirm('{{ __('Are you sure?') }}')"><i
                                                        class="fa fa-trash-o m-r-5"></i> {{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa fa-calendar-plus fa-3x mb-3 text-muted"></i>
                                        <h6>{{ __('No Accrual Plans Found') }}</h6>
                                        <p class="text-muted">
                                            {{ __('Create accrual plans to automate multi-tier leave allocation.') }}
                                        </p>
                                        <a href="{{ route('leave.config.accrual-plans.create') }}"
                                            class="btn btn-primary mt-2 btn-sm">
                                            {{ __('Create Your First Plan') }}
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