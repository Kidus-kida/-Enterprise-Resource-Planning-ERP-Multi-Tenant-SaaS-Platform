@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    @include('leave.partials.nav')

    <div class="leave-card">
        <div class="leave-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-exclamation-circle"></i> {{ __('Mandatory Days / Shutdowns') }}</h4>
            <a href="{{ route('leave.config.mandatory-days.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> {{ __('Create New') }}
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th width="50" class="text-center">#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Restriction') }}</th>
                        <th>{{ __('Applicability') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mandatoryDays as $day)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold">{{ $day->name }}</div>
                            <small class="text-muted">{{ Str::limit($day->restriction_message, 40) }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $day->date->format('d M, Y') }}</div>
                            <small class="text-muted">{{ $day->date->format('l') }}</small>
                        </td>
                        <td>
                            @if($day->restriction_type == 'no_leave')
                                <span class="badge bg-danger">{{ __('No Leave Allowed') }}</span>
                            @elseif($day->restriction_type == 'requires_approval')
                                <span class="badge bg-warning text-dark">{{ __('Approval Required') }}</span>
                            @else
                                <span class="badge bg-info text-dark">{{ __('Warning Only') }}</span>
                            @endif
                        </td>
                        <td>
                            @if(empty($day->applicable_departments) && empty($day->applicable_designations))
                                <span class="badge bg-success">{{ __('Everyone') }}</span>
                            @else
                                @if(!empty($day->applicable_departments))
                                    <div><small class="text-muted">{{ __('Depts:') }} {{ count($day->applicable_departments) }}</small></div>
                                @endif
                                @if(!empty($day->applicable_designations))
                                    <div><small class="text-muted">{{ __('Roles:') }} {{ count($day->applicable_designations) }}</small></div>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($day->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown dropdown-action">
                                <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('leave.config.mandatory-days.edit', $day->id) }}"><i class="fa fa-pencil m-r-5"></i> {{ __('Edit') }}</a>
                                    <form action="{{ route('leave.config.mandatory-days.destroy', $day->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fa fa-exclamation-circle mb-3"></i>
                                <h5>{{ __('No Mandatory Days Found') }}</h5>
                                <p>{{ __('Define days where leave is restricted (e.g. Company Retreat, Important Deadlines).') }}</p>
                                <a href="{{ route('leave.config.mandatory-days.create') }}" class="btn btn-primary mt-2">
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
