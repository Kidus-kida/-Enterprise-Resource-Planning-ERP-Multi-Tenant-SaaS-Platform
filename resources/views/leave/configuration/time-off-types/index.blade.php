@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-list"></i> {{ __('Time Off Types') }}</h4>
            <a href="{{ route('leave.config.time-off-types.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> {{ __('Create New') }}
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Color') }}</th>
                        <th>{{ __('Mode') }}</th>
                        <th>{{ __('Allocation') }}</th>
                        <th>{{ __('Approval') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveTypes as $type)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold">{{ $type->type_name }}</div>
                            <small class="text-muted">{{ $type->description }}</small>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $type->color ?? '#0d6efd' }}; color: #fff;">
                                {{ $type->color ?? '#0d6efd' }}
                            </span>
                        </td>
                        <td>
                            @if($type->is_paid)
                                <span class="badge bg-success">{{ __('Paid') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Unpaid') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($type->uses_accrual)
                                <span class="badge bg-info text-dark">{{ __('Accrual Plan') }}</span>
                                @if($type->accrualPlan)
                                    <br><small>{{ $type->accrualPlan->name }}</small>
                                @endif
                            @else
                                <span class="badge bg-light text-dark">{{ __('Manual/Fixed') }}</span>
                                <br><small>{{ $type->max_date_allowed }} {{ __('days') }}</small>
                            @endif
                        </td>
                        <td>
                            @if($type->requires_approval)
                                <span class="badge bg-warning text-dark">{{ __('Required') }}</span>
                                <small>({{ $type->approval_levels }} {{ __('level') }})</small>
                            @else
                                <span class="badge bg-success">{{ __('Auto-Approved') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($type->status == 'allowed' || $type->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('leave.config.time-off-types.edit', $type->id) }}"><i class="fa fa-pencil m-r-5"></i> {{ __('Edit') }}</a>
                                    <form action="{{ route('leave.config.time-off-types.destroy', $type->id) }}" method="POST" class="d-inline">
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
                                <i class="fa fa-list mb-3"></i>
                                <h5>{{ __('No Time Off Types Found') }}</h5>
                                <p>{{ __('Get started by creating a new time off type.') }}</p>
                                <a href="{{ route('leave.config.time-off-types.create') }}" class="btn btn-primary mt-2">
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
