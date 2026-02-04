@extends('layouts.app')

@push('page-styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h3 class="page-title">{{ $pageTitle }}</h3>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Pending Approvals -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Pending Attendance Approvals') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($approvals->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="la la-check-circle" style="font-size: 48px; color: #28a745;"></i>
                            <p class="mt-2">{{ __('No pending approvals') }}</p>
                            <small>{{ __('All manual attendance entries have been processed.') }}</small>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Submission Date') }}</th>
                                        <th>{{ __('Entries') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvals as $key => $group)
                                    @php
                                        $first = $group->first();
                                        $employee = $first->employee;
                                        $ids = $group->pluck('id')->toArray();
                                        $idsJson = json_encode($ids);
                                        $status = $first->status;
                                        $approverName = $first->approvedBy ? $first->approvedBy->name : '';
                                    @endphp
                                    <tr class="{{ $status != 'submitted' ? 'table-light text-muted' : '' }}">
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#" class="avatar"><img src="{{ $employee->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($employee->name ?? 'User') }}" alt=""></a>
                                                <a href="#">{{ $employee->name ?? 'Unknown' }} <span>{{ $employee->job_title ?? 'Employee' }}</span></a>
                                            </h2>
                                        </td>
                                        <td>
                                            {{ $first->submitted_at ? $first->submitted_at->format('M d, Y h:i A') : '-' }}
                                            <div class="small text-muted">{{ $first->submitted_at ? $first->submitted_at->diffForHumans() : '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $status == 'submitted' ? 'bg-info' : 'bg-secondary' }}">{{ $group->count() }}</span>
                                            @if($status == 'approved')
                                                <div class="mt-1"><span class="badge bg-success">{{ __('Approved') }}</span></div>
                                                <div class="small text-muted mt-1">{{ __('By:') }} {{ $approverName }}</div>
                                            @elseif($status == 'rejected')
                                                <div class="mt-1"><span class="badge bg-danger">{{ __('Rejected') }}</span></div>
                                                <div class="small text-muted mt-1">{{ __('By:') }} {{ $approverName }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-info view-approval-details me-1" 
                                                    data-employee="{{ $employee->name }}"
                                                    data-date="{{ $first->submitted_at ? $first->submitted_at->format('M d, Y') : '' }}"
                                                    data-details="{{ json_encode($group->map(function($g) {
                                                        return [
                                                            'date' => $g->attendance_date->format('M d, Y'),
                                                            'time_in' => \Carbon\Carbon::parse($g->time_in)->format('h:i A'),
                                                            'time_out' => $g->time_out ? \Carbon\Carbon::parse($g->time_out)->format('h:i A') : '-',
                                                            'reason' => $g->reason,
                                                            'status' => $g->status,
                                                            'rejection_reason' => $g->rejection_reason
                                                        ];
                                                    })) }}">
                                                <i class="la la-eye"></i>
                                            </button>
                                            
                                            @if($status == 'submitted')
                                            <button class="btn btn-sm btn-success approve-batch-btn me-1" 
                                                    data-ids="{{ $idsJson }}" 
                                                    title="{{ __('Approve Batch') }}">
                                                <i class="la la-check"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm btn-danger reject-batch-btn" 
                                                    data-ids="{{ $idsJson }}" 
                                                    title="{{ __('Reject Batch') }}">
                                                <i class="la la-times"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- /Pending Approvals -->
        
        <!-- Batch Approve Modal -->
        <div id="batch_approve_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>{{ __('Approve Batch') }}</h3>
                            <p>{{ __('Are you sure want to approve these attendance entries?') }}</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <input type="hidden" id="batch_approve_ids">
                            <div class="row">
                                <div class="col-6">
                                    <a href="javascript:void(0);" class="btn btn-primary continue-btn" id="confirmBatchApprove">{{ __('Approve') }}</a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Batch Approve Modal -->

        <!-- Batch Reject Modal -->
        <div id="batch_reject_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>{{ __('Reject Batch') }}</h3>
                            <p>{{ __('Please provide a reason for rejection:') }}</p>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <textarea class="form-control" id="batch_reject_reason" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-btn delete-action mt-3">
                            <input type="hidden" id="batch_reject_ids">
                            <div class="row">
                                <div class="col-6">
                                    <a href="javascript:void(0);" class="btn btn-danger continue-btn" id="confirmBatchReject">{{ __('Reject') }}</a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn">{{ __('Cancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Batch Reject Modal -->
        
        <!-- View Details Modal -->
        <div id="approval_details_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Submission Details') }} - <span id="modal_employee_name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Time In') }}</th>
                                        <th>{{ __('Time Out') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="modal_details_body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /View Details Modal -->

    </div>
@endsection

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Helper to show modal safely (Bootstrap 5 or jQuery)
    function showModal(modalId) {
        const modalEl = document.getElementById(modalId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            new bootstrap.Modal(modalEl).show();
        } else if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery(modalEl).modal('show');
        } else {
            console.error('Bootstrap or jQuery modal not found');
        }
    }

    // View Details Click
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.view-approval-details');
        if (btn) {
            e.preventDefault();
            const employee = btn.dataset.employee;
            const details = JSON.parse(btn.dataset.details);
            
            document.getElementById('modal_employee_name').textContent = employee;
            const tbody = document.getElementById('modal_details_body');
            tbody.innerHTML = '';
            
            details.forEach(entry => {
                tbody.innerHTML += `
                    <tr>
                        <td>${entry.date}</td>
                        <td>${entry.time_in}</td>
                        <td>${entry.time_out}</td>
                        <td>${entry.reason ? entry.reason.substring(0, 50) : '-'}</td>
                    </tr>
                `;
            });
            
            showModal('approval_details_modal');
        }
    });

    // Batch Approve Click
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.approve-batch-btn');
        if (btn) {
            e.preventDefault();
            document.getElementById('batch_approve_ids').value = btn.dataset.ids;
            showModal('batch_approve_modal');
        }
    });
    
    // Confirm Batch Approve
    document.getElementById('confirmBatchApprove').addEventListener('click', function() {
        const ids = JSON.parse(document.getElementById('batch_approve_ids').value);
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        fetch('{{ route("attendance.approvals.approve_batch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
                btn.textContent = 'Approve';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error occurred');
            btn.disabled = false;
            btn.textContent = 'Approve';
        });
    });
    
    // Batch Reject Click
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.reject-batch-btn');
        if (btn) {
            e.preventDefault();
            document.getElementById('batch_reject_ids').value = btn.dataset.ids;
            document.getElementById('batch_reject_reason').value = '';
            showModal('batch_reject_modal');
        }
    });
    
    // Confirm Batch Reject
    document.getElementById('confirmBatchReject').addEventListener('click', function() {
        const ids = JSON.parse(document.getElementById('batch_reject_ids').value);
        const reason = document.getElementById('batch_reject_reason').value;
        
        if(!reason.trim()) {
            alert('Please provide a rejection reason');
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';
        
        fetch('{{ route("attendance.approvals.reject_batch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error occurred');
                btn.disabled = false;
                btn.textContent = 'Reject';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error occurred');
            btn.disabled = false;
            btn.textContent = 'Reject';
        });
    });
});
</script>
@endpush
