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

        <!-- Attendance Entry Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">{{ __('Add Team Attendance') }}</h4>
                        
                        <div class="alert alert-info">
                            <i class="la la-info-circle"></i>
                            <strong>{{ __('Team Entry Mode') }}</strong><br>
                            {{ __('You can add attendance entries for yourself AND other team members.') }}
                            @if($maxDaysBack > 0)
                                <br>{{ __('Maximum') }}: {{ $maxDaysBack }} {{ __('days back') }}
                            @endif
                            @if($allowFuture)
                                <br>{{ __('Future entries are allowed') }}
                            @endif
                        </div>

                        <form method="POST" action="#" class="mt-4">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Employee') }}</label>
                                        <select class="form-select select2" name="employee_id" required>
                                            <option value="">{{ __('Select Employee') }}</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->firstname }} {{ $employee->lastname }} ({{ $employee->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Date') }}</label>
                                        <input type="date" class="form-control" name="attendance_date" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Time In') }}</label>
                                        <input type="time" class="form-control" name="time_in" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Time Out') }}</label>
                                        <input type="time" class="form-control" name="time_out" required>
                                    </div>
                                </div>
                            </div>

                            @if($requireReason)
                            <div class="mb-3">
                                <label class="form-label">{{ __('Reason') }}</label>
                                <textarea class="form-control" name="reason" rows="3" required placeholder="{{ __('Please provide a reason for this manual entry...') }}"></textarea>
                            </div>
                            @endif

                            @if($trackProject)
                            <div class="mb-3">
                                <label class="form-label">{{ $requireProject ? __('Project') : __('Project (Optional)') }}</label>
                                <select class="form-select" name="project_id" {{ $requireProject ? 'required' : '' }}>
                                    <option value="">{{ __('Select Project') }}</option>
                                    <!-- Projects will be populated here -->
                                </select>
                            </div>
                            @endif

                            <div class="mt-4">
                                <button type="submit" name="action" value="submit" class="btn btn-primary">
                                    <i class="la la-check-circle"></i> {{ __('Submit Attendance') }}
                                </button>
                                <button type="submit" name="action" value="draft" class="btn btn-secondary">
                                    <i class="la la-save"></i> {{ __('Save as Draft') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Attendance Entry Form -->

        <!-- Saved Drafts by Employee -->
        @if(isset($drafts) && $drafts->isNotEmpty())
        <div class="row mt-4">
            <div class="col-md-12">
                <h4 class="mb-3">{{ __('Saved Drafts (Ready to Submit)') }}</h4>
                @foreach($drafts as $employeeId => $group)
                    @php 
                        $employeeName = $group->first()->employee->name ?? 'Unknown Employee'; 
                        $ids = $group->pluck('id')->toArray();
                    @endphp
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="la la-user"></i> {{ $employeeName }} 
                                <span class="badge bg-secondary ms-2">{{ $group->count() }}</span>
                            </h5>
                            <button class="btn btn-success btn-sm submit-manual-batch" data-ids="{{ json_encode($ids) }}">
                                <i class="la la-paper-plane"></i> {{ __('Submit All for') }} {{ $employeeName }}
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Time') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group as $draft)
                                        <tr>
                                            <td>{{ $draft->attendance_date->format('M d, Y') }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($draft->time_in)->format('h:i A') }} - 
                                                {{ $draft->time_out ? \Carbon\Carbon::parse($draft->time_out)->format('h:i A') : 'Ongoing' }}
                                            </td>
                                            <td>{{ $draft->reason }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('attendance.my.draft.delete', $draft->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this draft?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="la la-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        <!-- /Saved Drafts -->

        <!-- Submission History (Summary Table) -->
        @if(isset($history) && $history->isNotEmpty())
        <div class="row mt-4">
            <div class="col-md-12">
                <h4 class="mb-3">{{ __('Submission History') }}</h4>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Past Submissions') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Submission Date') }}</th>
                                        <th>{{ __('Total Entries') }}</th>
                                        <th>{{ __('Status Summary') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $timestamp => $batch)
                                        @php 
                                            $batchDate = \Carbon\Carbon::parse($timestamp); 
                                            // Calculate Summary
                                            $counts = $batch->groupBy('status')->map->count();
                                            $summary = [];
                                            if(isset($counts['approved'])) $summary[] = $counts['approved'] . ' Approved';
                                            if(isset($counts['rejected'])) $summary[] = $counts['rejected'] . ' Rejected';
                                            if(isset($counts['submitted'])) $summary[] = $counts['submitted'] . ' Pending';
                                            $summaryText = implode(', ', $summary);
                                        @endphp
                                        <tr>
                                            <td>
                                                <i class="la la-clock text-muted"></i> {{ $batchDate->format('M d, Y h:i A') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $batch->count() }}</span>
                                            </td>
                                            <td>
                                                {{ $summaryText ?: 'Unknown' }}
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-info view-batch-details" 
                                                    data-details="{{ json_encode($batch->map(function($entry) {
                                                        return [
                                                            'employee' => $entry->employee->name ?? 'Unknown',
                                                            'avatar' => strtoupper(substr($entry->employee->firstname ?? 'U', 0, 1)),
                                                            'date' => $entry->attendance_date->format('M d, Y'),
                                                            'time' => \Carbon\Carbon::parse($entry->time_in)->format('h:i A') . ' - ' . ($entry->time_out ? \Carbon\Carbon::parse($entry->time_out)->format('h:i A') : 'Ongoing'),
                                                            'status' => $entry->status,
                                                            'approved_by' => $entry->approvedBy ? $entry->approvedBy->name : null,
                                                            'rejection_reason' => $entry->rejection_reason
                                                        ];
                                                    })) }}">
                                                    <i class="la la-eye"></i> {{ __('View') }}
                                                </button>
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
        @endif
        <!-- /Submission History -->

        <!-- Batch Details Modal -->
        <div class="modal fade" id="batchDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Batch Details') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Date & Time') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Approver') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="batchDetailsBody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Handle Batch Submission
            document.querySelectorAll('.submit-manual-batch').forEach(btn => {
                btn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if(!confirm('{{ __("Submit these drafts for approval?") }}')) return;
                    
                    const ids = JSON.parse(this.dataset.ids);
                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="la la-spinner la-spin"></i> Processing...';

                    fetch('{{ route("attendance.submitManual") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Unknown error'));
                            this.disabled = false;
                            this.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Network Error');
                        this.disabled = false;
                        this.innerHTML = originalText;
                    });
                };
            });

            // Handle View Details Modal
            const modalEl = document.getElementById('batchDetailsModal');
            let batchModal;
            if (typeof bootstrap !== 'undefined') {
                 batchModal = new bootstrap.Modal(modalEl);
            } else {
                // Fallback or jQuery if needed, but let's assume Bootstrap 5
                // If pure JS bootstrap not available, checking for window.bootstrap
            }

            document.querySelectorAll('.view-batch-details').forEach(btn => {
                btn.addEventListener('click', function() {
                    const details = JSON.parse(this.dataset.details);
                    const tbody = document.getElementById('batchDetailsBody');
                    tbody.innerHTML = '';

                    details.forEach(entry => {
                        let statusBadge = '';
                        if (entry.status === 'submitted') statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                        else if (entry.status === 'approved') statusBadge = '<span class="badge bg-success">Approved</span>';
                        else if (entry.status === 'rejected') statusBadge = '<span class="badge bg-danger">Rejected</span>';

                        let approverInfo = entry.approved_by || '-';
                        if (entry.status === 'rejected' && entry.rejection_reason) {
                            approverInfo += `<br><small class="text-danger">${entry.rejection_reason}</small>`;
                        }

                        const row = `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                            ${entry.avatar}
                                        </span>
                                        ${entry.employee}
                                    </div>
                                </td>
                                <td>
                                    <div>${entry.date}</div>
                                    <small class="text-muted">${entry.time}</small>
                                </td>
                                <td>${statusBadge}</td>
                                <td>${approverInfo}</td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });

                    // Open Modal
                    if (typeof bootstrap !== 'undefined' && batchModal) {
                        batchModal.show();
                    } else if (typeof $ !== 'undefined') {
                        $('#batchDetailsModal').modal('show');
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
