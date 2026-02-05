<div class="modal-body">
    <form id="attendance_correction_form">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="la la-user-circle fs-4"></i>
                        <div>
                            <strong>{{ $user->fullname }}</strong><br>
                            <span class="small">{{ __('Correcting Attendance for:') }} {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $firstTimestamp = $timestamps->first();
                $startTimeValue = $firstTimestamp && $firstTimestamp->startTime ? $firstTimestamp->startTime->format('H:i') : '';
                $endTimeValue = $firstTimestamp && $firstTimestamp->endTime ? $firstTimestamp->endTime->format('H:i') : '';
            @endphp

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">{{ __('Clock-In Time') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="la la-clock"></i></span>
                        <input type="time" name="start_time" class="form-control" value="{{ $startTimeValue }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label fw-bold">{{ __('Clock-Out Time') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="la la-clock"></i></span>
                        <input type="time" name="end_time" class="form-control" value="{{ $endTimeValue }}">
                    </div>
                </div>
            </div>

            <div class="col-12 mt-3">
                <div class="form-group">
                    <label class="form-label fw-bold">{{ __('Reason for Correction') }} @if(\App\Models\AttendanceSetting::get('correction_require_reason', true)) <span class="text-danger">*</span> @endif</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="{{ __('Provide a reason for this manual override...') }}" {{ \App\Models\AttendanceSetting::get('correction_require_reason', true) ? 'required' : '' }}></textarea>
                </div>
            </div>
        </div>

        <div class="submit-section mt-4 text-end">
            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="button" onclick="submitCorrection()" class="btn btn-primary submit-btn px-4">
                <i class="la la-save me-1"></i> {{ __('Apply Correction') }}
            </button>
        </div>
    </form>
</div>

<script>
    function submitCorrection() {
        const form = document.getElementById('attendance_correction_form');
        const formData = new FormData(form);
        const btn = form.querySelector('.submit-btn');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="la la-spinner la-spin"></i> {{ __("Applying...") }}';

        fetch("{{ route('admin.attendances.update') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                $('#attendance_info').modal('hide'); // Assuming the generic modal ID is attendance_info or similar
                // Refresh the page to see changes
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || "{{ __('Something went wrong') }}");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error("{{ __('An error occurred while saving.') }}");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="la la-save me-1"></i> {{ __('Apply Correction') }}';
        });
    }
</script>
