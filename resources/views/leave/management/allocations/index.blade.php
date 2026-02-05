@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-chart-pie"></i> {{ __('Leave Allocations') }}</h4>
            <a href="{{ route('leave.management.allocations.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> {{ __('New Allocation') }}
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Leave Type') }}</th>
                        <th>{{ __('Days Allocated') }}</th>
                        <th>{{ __('Days Remaining') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Year') }}</th>
                        <th>{{ __('Notes') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $allocation)
                    <tr>
                        <td class="text-center">{{ $loop->iteration + ($allocations->currentPage() - 1) * $allocations->perPage() }}</td>
                        <td>
                            @if($allocation->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        {{ substr($allocation->user->firstname, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $allocation->user->firstname }} {{ $allocation->user->lastname }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">{{ __('Unknown User') }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $allocation->leaveType->color ?? '#6c757d' }}; color: #fff;">
                                {{ $allocation->leaveType->type_name }}
                            </span>
                        </td>
                        <td>{{ $allocation->allocated_days }}</td>
                        <td>
                            @if($allocation->available_days < 0)
                                <span class="text-danger fw-bold">{{ $allocation->available_days }}</span>
                            @else
                                <span class="text-success fw-bold">{{ $allocation->available_days }}</span>
                            @endif
                        </td>
                        <td>
                            @if($allocation->status == 'pending')
                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                            @elseif($allocation->status == 'rejected')
                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                            @else
                                <span class="badge bg-success">{{ __('Approved') }}</span>
                            @endif
                        </td>
                        <td>{{ $allocation->year }}</td>
                        <td><small class="text-muted">{{ Str::limit($allocation->notes, 30) }}</small></td>
                        <td class="text-end">
                            <div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if($allocation->status == 'pending')
                                        <form action="{{ route('leave.management.allocations.update', $allocation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_action" value="approve">
                                            <button type="submit" class="dropdown-item"><i class="fa fa-check m-r-5"></i> {{ __('Approve') }}</button>
                                        </form>
                                        <form action="{{ route('leave.management.allocations.update', $allocation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_action" value="reject">
                                            <button type="submit" class="dropdown-item"><i class="fa fa-times m-r-5"></i> {{ __('Reject') }}</button>
                                        </form>
                                    @else
                                        <a class="dropdown-item" href="{{ route('leave.management.allocations.edit', $allocation->id) }}"><i class="fa fa-pencil m-r-5"></i> {{ __('Edit') }}</a>
                                    @endif
                                    
                                    <form action="{{ route('leave.management.allocations.destroy', $allocation->id) }}" method="POST" class="d-inline">
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
                                <i class="fa fa-chart-pie mb-3"></i>
                                <h5>{{ __('No Allocations Found') }}</h5>
                                <p>{{ __('Manually allocate leave days to employees.') }}</p>
                                <a href="{{ route('leave.management.allocations.create') }}" class="btn btn-primary mt-2">
                                    {{ __('New Allocation') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $allocations->links() }}
        </div>
    </div>
</div>
@endsection
