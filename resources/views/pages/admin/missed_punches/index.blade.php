@extends('layouts.app')

@section('title', $pageTitle)

@section('page-content')
    <div class="content container-fluid">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('attendances.index') }}">{{ __('Attendances') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Missed Punch Approvals') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Punch Type') }}</th>
                                        <th>{{ __('Requested Times') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Decision') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>
                                                <h2 class="table-avatar">
                                                    <a href="#" class="avatar"><img src="{{ $request->user->avatar ? asset($request->user->avatar) : asset('assets/img/profiles/avatar-02.jpg') }}" alt=""></a>
                                                    <a href="#">{{ $request->user->firstname }} {{ $request->user->lastname }} <span>{{ $request->user->employeeDetail->designation->name ?? '' }}</span></a>
                                                </h2>
                                            </td>
                                            <td>{{ $request->date->format('M d, Y') }}</td>
                                            <td>
                                                @if($request->punch_type === 'clock_in')
                                                    <span class="badge bg-inverse-info">{{ __('Clock-In') }}</span>
                                                @elseif($request->punch_type === 'clock_out')
                                                    <span class="badge bg-inverse-warning">{{ __('Clock-Out') }}</span>
                                                @else
                                                    <span class="badge bg-inverse-purple">{{ __('Both') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->punch_type === 'clock_in')
                                                    {{ $request->requested_start_time->format('H:i') }}
                                                @elseif($request->punch_type === 'clock_out')
                                                    {{ $request->requested_end_time->format('H:i') }}
                                                @else
                                                    {{ $request->requested_start_time->format('H:i') }} - {{ $request->requested_end_time->format('H:i') }}
                                                @endif
                                            </td>
                                            <td>
                                                <span title="{{ $request->reason }}">
                                                    {{ Str::limit($request->reason, 25) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown action-label">
                                                    @if($request->status === 'pending')
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-warning"></i> {{ __('Pending') }}
                                                        </a>
                                                    @elseif($request->status === 'approved')
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-success"></i> {{ __('Accepted') }}
                                                        </a>
                                                    @else
                                                        <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                            <i class="fa fa-dot-circle-o text-danger"></i> {{ __('Rejected') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($request->status === 'approved')
                                                    <span class="text-success fw-bold"><i class="fa fa-check-circle"></i> {{ __('Accepted') }}</span>
                                                @elseif($request->status === 'rejected')
                                                    <span class="text-danger fw-bold"><i class="fa fa-times-circle"></i> {{ __('Rejected') }}</span>
                                                @else
                                                    <span class="text-muted small"><i>{{ __('Waiting...') }}</i></span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @if($request->status === 'pending')
                                                        <button class="btn btn-sm btn-success" onclick="openApproveModal({{ $request->id }})" title="{{ __('Approve') }}">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="openRejectModal({{ $request->id }})" title="{{ __('Reject') }}">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    @else
                                                        <div class="text-muted small me-2">
                                                            <i class="fa fa-lock"></i> {{ __('Processed') }}
                                                        </div>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-danger" onclick="openDeleteModal({{ $request->id }})" title="{{ __('Delete') }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                {{ __('No missed punch requests found.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($requests->hasPages())
                        <div class="card-footer bg-white border-top-0">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal custom-modal fade" id="approve_modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('Approve Request') }}</h3>
                        <p>{{ __('Are you sure you want to approve this correction? Attendance records will be updated.') }}</p>
                    </div>
                    <form id="approve_form" method="POST">
                        @csrf
                        <div class="modal-btn delete-action">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary continue-btn w-100">{{ __('Approve') }}</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal custom-modal fade" id="reject_modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('Reject Request') }}</h3>
                        <p>{{ __('Please provide a reason for rejecting this correction.') }}</p>
                    </div>
                    <form id="reject_form" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="{{ __('Reason for rejection...') }}"></textarea>
                        </div>
                        <div class="modal-btn delete-action">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary continue-btn w-100">{{ __('Reject') }}</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Delete Modal -->
    <div class="modal custom-modal fade" id="delete_modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="form-header">
                        <h3>{{ __('Delete Request') }}</h3>
                        <p>{{ __('Are you sure you want to delete this record? This action cannot be undone.') }}</p>
                    </div>
                    <form id="delete_form" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-btn delete-action">
                            <div class="row">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-danger continue-btn w-100">{{ __('Delete') }}</button>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
<script>
    function openApproveModal(id) {
        const form = document.getElementById('approve_form');
        let url = "{{ route('admin.missed-punches.approve', ':id') }}";
        form.action = url.replace(':id', id);
        $('#approve_modal').modal('show');
    }

    function openRejectModal(id) {
        const form = document.getElementById('reject_form');
        let url = "{{ route('admin.missed-punches.reject', ':id') }}";
        form.action = url.replace(':id', id);
        $('#reject_modal').modal('show');
    }

    function openDeleteModal(id) {
        const form = document.getElementById('delete_form');
        let url = "{{ route('admin.missed-punches.destroy', ':id') }}";
        form.action = url.replace(':id', id);
        $('#delete_modal').modal('show');
    }
</script>
@endpush
