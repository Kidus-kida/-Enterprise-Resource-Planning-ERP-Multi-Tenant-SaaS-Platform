{{-- resources/views/leaverequests/edit.blade.php --}}
<div class="modal-body">
    <form action="{{ route('leaverequests.update', [$leaverequest->id, $leaverequest->employee_id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            @can('edit-ticket')
                <!-- Start + End date (readonly) -->
                <div class="col-md-6">
                    <div class="input-block mb-3">
                        <x-form.label>{{ __('Leave Start Date —— Leave End Date') }}</x-form.label>
                        <x-form.input type="text" name="leave_start_date" readonly
                            value="{{ $leaverequest->leave_start_date }}  —  {{ $leaverequest->leave_end_date }}" />
                    </div>
                </div>

                <!-- Approve / Reject select -->
                <div class="col-md-6">
                    <div class="input-block mb-3">
                        <x-form.label for="statusSelect">{{ __('Action') }}</x-form.label>
                        <select name="status" id="statusSelect" class="form-control">
                            <option value="">{{ __('—  Select Approve / Reject  —') }}</option>
                            <option value="approved">approve</option>
                            <option value="rejected">reject</option>
                        </select>
                    </div>
                </div>
            @endcan
        </div>

        <!-- Reject‑reason section (hidden by default) -->
        <div class="row" id="reject-reason-section" style="display:none;">
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Reject Reason') }}</label>
                    <x-form.ckeditor name="reject_reason" id="reject_reason_editor" />
                </div>
            </div>
        </div>

        <!-- Always‑visible description -->
        <div class="row">
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Request Reason') }}</label>
                    <x-form.ckeditor name="request_reason" id="reject_reason_editor"
                        id="editor">{{ $leaverequest->request_reason }}</x-form.ckeditor>
                </div>
            </div>
        </div>

        <div class="submit-section my-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Update') }}</x-form.button>
        </div>
    </form>
</div>
