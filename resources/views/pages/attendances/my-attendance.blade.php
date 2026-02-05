@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h3 class="page-title">{{ __('My Attendance') }}</h3>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        
        <!-- Rejected Drafts Alert -->
        @if($drafts->where('status', 'rejected')->count() > 0)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('Action Required!') }}</strong> {{ __('You have rejected attendance entries. Please check the "Saved Drafts" list below, edit the rejected items, and re-submit them.') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Attendance Entry Form -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">{{ __('Add Attendance Entry') }}</h4>
                        
                        <div class="alert alert-info">
                            <i class="la la-info-circle"></i>
                            <strong>{{ __('Self-Service Mode') }}</strong><br>
                            {{ __('Save your attendance entries as drafts, then submit all together for approval.') }}
                            @if($maxDaysBack > 0)
                                <br>{{ __('Maximum') }}: {{ $maxDaysBack }} {{ __('days back') }}
                            @endif
                            @if($allowFuture)
                                <br>{{ __('Future entries are allowed') }}
                            @endif
                        </div>

                        <form id="draftForm" class="mt-4">
                            @csrf
                            <input type="hidden" id="editDraftId" value="">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Date') }}</label>
                                        <input type="date" class="form-control" name="attendance_date" id="attendance_date" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Time In') }}</label>
                                        <input type="time" class="form-control" name="time_in" id="time_in" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Time Out') }}</label>
                                        <input type="time" class="form-control" name="time_out" id="time_out">
                                    </div>
                                </div>
                            </div>

                            @if($requireReason)
                            <div class="mb-3">
                                <label class="form-label">{{ __('Reason') }}</label>
                                <textarea class="form-control" name="reason" id="reason" rows="3" required placeholder="{{ __('Please provide a reason for this manual entry...') }}"></textarea>
                            </div>
                            @endif

                            @if($trackProject)
                            <div class="mb-3">
                                <label class="form-label">{{ $requireProject ? __('Project') : __('Project (Optional)') }}</label>
                                <select class="form-select" name="project_id" id="project_id" {{ $requireProject ? 'required' : '' }}>
                                    <option value="">{{ __('Select Project') }}</option>
                                    <!-- Projects will be populated here -->
                                </select>
                            </div>
                            @endif

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary" id="saveDraftBtn">
                                    <i class="la la-save"></i> <span id="saveButtonText">{{ __('Save Draft') }}</span>
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display: none;">
                                    <i class="la la-times"></i> {{ __('Cancel Edit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Attendance Entry Form -->

        <!-- Draft Entries Table -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Saved Drafts') }} (<span id="draftsCount">{{ $drafts->count() }}</span>)</h5>
                        @if($drafts->count() > 0)
                        <button type="button" class="btn btn-success btn-sm" id="submitAllBtn" onclick="submitAllDrafts(event)">
                            <i class="la la-check"></i> {{ __('Submit All for Approval') }}
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div id="draftsTableContainer">
                            @if($drafts->count() > 0)
                            <div class="table-responsive" id="draftsTableContainer">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Time In') }}</th>
                                            <th>{{ __('Time Out') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="draftsTableBody">
                                        @foreach ($drafts as $draft)
                                <tr id="draft_row_{{ $draft->id }}" class="{{ $draft->status == 'rejected' ? 'table-danger' : '' }}">
                                    <td>
                                        {{ $draft->attendance_date->format('M d, Y') }}
                                        @if($draft->status == 'rejected')
                                            <div class="text-danger small"><i class="la la-exclamation-circle"></i> {{ __('Rejected') }}</div>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($draft->time_in)->format('h:i A') }}</td>
                                    <td>{{ $draft->time_out ? \Carbon\Carbon::parse($draft->time_out)->format('h:i A') : '-' }}</td>
                                    <td>
                                        {{ $draft->reason }}
                                        @if($draft->status == 'rejected' && $draft->rejection_reason)
                                            <div class="text-danger small mt-1">
                                                <strong>{{ __('Reason:') }}</strong> {{ $draft->rejection_reason }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-primary btn-sm edit-draft" 
                                            data-id="{{ $draft->id }}"
                                            data-date="{{ $draft->attendance_date->format('Y-m-d') }}"
                                            data-time-in="{{ \Carbon\Carbon::parse($draft->time_in)->format('H:i') }}"
                                            data-time-out="{{ $draft->time_out ? \Carbon\Carbon::parse($draft->time_out)->format('H:i') : '' }}"
                                            data-reason="{{ $draft->reason }}"
                                            data-project="{{ $draft->project_id }}">
                                            <i class="la la-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-draft" onclick="confirmDeleteDraft(event, {{ $draft->id }})">
                                            <i class="la la-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5 text-muted" id="noDraftsMessage">
                                <i class="la la-inbox" style="font-size: 48px;"></i>
                                <p class="mt-2">{{ __('No draft entries yet') }}</p>
                                <small>{{ __('Add attendance entries above and save them as drafts') }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Draft Entries Table -->
        
        <!-- Submission History -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Submission History') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($history->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Submission Date') }}</th>
                                        <th>{{ __('Entries') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $timestamp => $group)
                                    @php
                                        $statusCounts = $group->groupBy('status')->map->count();
                                        $mainStatus = 'submitted';
                                        if ($statusCounts->has('rejected')) $mainStatus = 'rejected';
                                        elseif ($statusCounts->has('approved') && $statusCounts->count() == 1) $mainStatus = 'approved';
                                        elseif ($statusCounts->has('approved')) $mainStatus = 'mixed';
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($timestamp)->format('M d, Y h:i A') }}</td>
                                        <td>{{ $group->count() }}</td>
                                        <td>
                                            @if($mainStatus == 'submitted')
                                                <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                                            @elseif($mainStatus == 'approved')
                                                <span class="badge bg-success">{{ __('Approved') }}</span>
                                            @elseif($mainStatus == 'rejected')
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @else
                                                <span class="badge bg-info">{{ __('Partially Processed') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($mainStatus == 'rejected')
                                            <button class="btn btn-sm btn-danger me-1 fix-draft-btn" onclick="scrollToFixDrafts(event)" data-bs-toggle="tooltip" title="{{ __('Scroll to Drafts to Fix') }}">
                                                <i class="la la-edit"></i> {{ __('Fix') }}
                                            </button>
                                            @endif
                                            
                                            <button class="btn btn-sm btn-outline-info view-submission" 
                                                    data-date="{{ \Carbon\Carbon::parse($timestamp)->format('M d, Y h:i A') }}"
                                                    data-details="{{ json_encode($group->map(function($g) {
                                                        return [
                                                            'date' => $g->attendance_date->format('M d, Y'),
                                                            'time_in' => \Carbon\Carbon::parse($g->time_in)->format('h:i A'),
                                                            'time_out' => $g->time_out ? \Carbon\Carbon::parse($g->time_out)->format('h:i A') : '-',
                                                            'reason' => $g->reason,
                                                            'status' => $g->status,
                                                            'rejection_reason' => $g->rejection_reason,
                                                            'approver' => $g->approvedBy ? $g->approvedBy->name : null
                                                        ];
                                                    })) }}">
                                                <i class="la la-eye"></i> {{ __('View Details') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5 text-muted">
                            <i class="la la-history" style="font-size: 48px;"></i>
                            <p class="mt-2">{{ __('No submission history') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- /Submission History -->

    </div>

    <!-- Submission Details Modal -->
    <div id="submission_details_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Submission Details') }} - <span id="modal_submission_date"></span></h5>
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
                                    <th class="text-end">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="modal_submission_body">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Submission Details Modal -->

@endsection

@push('page-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Draft form script initialized (Vanilla JS)');
    
    const draftForm = document.getElementById('draftForm');
    const editDraftId = document.getElementById('editDraftId');
    const saveButtonText = document.getElementById('saveButtonText');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const submitAllBtn = document.getElementById('submitAllBtn');
    const draftsCount = document.getElementById('draftsCount');
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Save/Update Draft
    if (draftForm) {
        draftForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            const draftId = editDraftId.value;
            const isEdit = draftId !== '';
            let url = isEdit 
                ? '{{ route("attendance.my.draft.update", ":id") }}'.replace(':id', draftId)
                : '{{ route("attendance.my.draft.save") }}';
            const method = isEdit ? 'PUT' : 'POST';
            
            const formData = {
                attendance_date: document.getElementById('attendance_date')?.value || '',
                time_in: document.getElementById('time_in')?.value || '',
                time_out: document.getElementById('time_out')?.value || null,
                reason: document.getElementById('reason')?.value || null,
                project_id: document.getElementById('project_id')?.value || null,
                _token: csrfToken
            };
            
            if (method === 'PUT') {
                formData._method = 'PUT';
            }
            
            console.log('Sending request to:', url);
            console.log('Method:', method);
            console.log('Data:', formData);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message || 'An error occurred', 'error');
            });
        });
    }
    
    // Edit Draft
    document.querySelectorAll('.edit-draft').forEach(btn => {
        btn.addEventListener('click', function() {
            editDraftId.value = this.dataset.id;
            
            const dateInput = document.getElementById('attendance_date');
            if (dateInput) dateInput.value = this.dataset.date;
            
            const timeInInput = document.getElementById('time_in');
            if (timeInInput) timeInInput.value = this.dataset.timeIn;
            
            const timeOutInput = document.getElementById('time_out');
            if (timeOutInput) timeOutInput.value = this.dataset.timeOut;
            
            const reasonInput = document.getElementById('reason');
            if (reasonInput) reasonInput.value = this.dataset.reason;
            
            const projectInput = document.getElementById('project_id');
            if (projectInput) projectInput.value = this.dataset.project;
            
            if (saveButtonText) saveButtonText.textContent = '{{ __("Update Draft") }}';
            if (cancelEditBtn) cancelEditBtn.style.display = 'inline-block';
            
            // Scroll to form
            if (draftForm) draftForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // Cancel Edit
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            resetForm();
        });
    }
    
    // Delete Draft
    // Global Delete Handler
    window.confirmDeleteDraft = function(e, draftId) {
        if(e) e.preventDefault();
        
        if (!confirm('{{ __("Are you sure you want to delete this draft?") }}')) {
            return;
        }
        
        const url = '{{ route("attendance.my.draft.delete", ":id") }}'.replace(':id', draftId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const row = document.getElementById('draft_row_' + draftId);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        updateDraftsCount();
                    }, 300);
                }
            } else {
                 showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'An error occurred', 'error');
        });
    };
    
    // Global Submit All Handler
    window.submitAllDrafts = function(e) {
        if(e) e.preventDefault();
        
        const draftsCount = document.getElementById('draftsCount');
        const count = draftsCount ? draftsCount.textContent : 'some';
        
        if (!confirm(`{{ __("Submit all :count drafts for approval?") }}`.replace(':count', count))) {
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch('{{ route("attendance.my.submit") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(error.message || 'An error occurred', 'error');
        });
    };
    
    // Helper Functions
    function resetForm() {
        draftForm.reset();
        editDraftId.value = '';
        saveButtonText.textContent = '{{ __("Save Draft") }}';
        cancelEditBtn.style.display = 'none';
    }
    
    function updateDraftsCount() {
        const count = document.querySelectorAll('#draftsTableBody tr').length;
        draftsCount.textContent = count;
        
        if (count === 0) {
            document.getElementById('draftsTableContainer').innerHTML = `
                <div class="text-center py-5 text-muted" id="noDraftsMessage">
                    <i class="la la-inbox" style="font-size: 48px;"></i>
                    <p class="mt-2">{{ __('No draft entries yet') }}</p>
                    <small>{{ __('Add attendance entries above and save them as drafts') }}</small>
                </div>
            `;
            if (submitAllBtn) {
                submitAllBtn.style.display = 'none';
            }
        }
    }
    
    function showNotification(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'la-check-circle' : 'la-exclamation-circle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.setAttribute('role', 'alert');
        alert.innerHTML = `
            <i class="la ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        draftForm.insertBefore(alert, draftForm.firstChild);
        
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
    
    // Fix Draft Button Handler (Global to avoid duplicates)
    window.scrollToFixDrafts = function(e) {
        if (e) e.preventDefault();
        
        const container = document.getElementById('draftsTableContainer');
        if (container) {
            // Scroll
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Highlight
            const rejectedRows = container.querySelectorAll('.table-danger');
            
            if (rejectedRows.length > 0) {
                rejectedRows.forEach(row => {
                    row.classList.remove('table-danger'); // Remove permanent danger class temporarily
                    row.classList.add('bg-warning'); // Flash yellow
                    row.style.transition = 'background-color 0.5s';
                    
                    setTimeout(() => {
                        row.classList.remove('bg-warning');
                        row.classList.add('table-danger'); // Restore red
                    }, 1000);
                });
            } else {
                console.warn('No rejected rows found to highlight');
            }
        }
    };

    // View Submission Details
    document.addEventListener('click', function(e) {
        // console.log('Click detected on:', e.target);
        const btn = e.target.closest('.view-submission');
        if (btn) {
            console.log('View button clicked');
            e.preventDefault(); // Prevent any default action
            
            try {
                const date = btn.dataset.date;
                console.log('Date:', date);
                
                const rawDetails = btn.dataset.details;
                // console.log('Raw details:', rawDetails);
                
                const details = JSON.parse(rawDetails);
                console.log('Parsed details:', details);
                
                document.getElementById('modal_submission_date').textContent = date;
                const tbody = document.getElementById('modal_submission_body');
                tbody.innerHTML = '';
                
                details.forEach(entry => {
                    let statusBadge = '';
                    if (entry.status === 'submitted') {
                         statusBadge = '<span class="badge bg-warning text-dark">{{ __("Pending") }}</span>';
                    }
                    else if (entry.status === 'approved') {
                        statusBadge = `<span class="badge bg-success">{{ __("Approved") }}</span>`;
                        if(entry.approver) statusBadge += `<div class="small text-muted mt-1">{{ __("By:") }} ${entry.approver}</div>`;
                    }
                    else if (entry.status === 'rejected') {
                        statusBadge = `<span class="badge bg-danger">{{ __("Rejected") }}</span>`;
                        if(entry.approver) statusBadge += `<div class="small text-muted mt-1">{{ __("By:") }} ${entry.approver}</div>`;
                    }
                    
                    tbody.innerHTML += `
                        <tr>
                            <td>${entry.date}</td>
                            <td>${entry.time_in}</td>
                            <td>${entry.time_out}</td>
                            <td>
                                ${entry.reason ? entry.reason.substring(0, 50) : '-'}
                                ${entry.status === 'rejected' && entry.rejection_reason ? `<div class="text-danger small mt-1"><strong>{{ __("Reason:") }}</strong> ${entry.rejection_reason}</div>` : ''}
                            </td>
                            <td class="text-end">${statusBadge}</td>
                        </tr>
                    `;
                });
                
                const modalEl = document.getElementById('submission_details_modal');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    console.log('Bootstrap is executing');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    console.error('Bootstrap 5 JS not detected!');
                    // Fallback for older Bootstrap (jQuery based)
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                         console.log('Trying jQuery modal');
                         jQuery(modalEl).modal('show');
                    } else {
                        alert('Error: Bootstrap modal library not found.');
                    }
                }
            } catch (err) {
                console.error('Error in view details:', err);
            }
        }
    });
});
</script>
@endpush

