<div class="modal-body">
    <form action="{{ route('leaverequests.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Leave Balance Display -->
            <div class="col-md-12">
                @if(isset($allocations) && count($allocations) > 0)
                    <div class="mb-3">
                        <h6 class="text-muted fw-bold mb-2">{{ __('Your Available Balances:') }}</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($allocations as $type => $days)
                                <div class="badge bg-light text-dark border p-2 d-flex align-items-center">
                                    <span class="me-2">{{ $type }}:</span>
                                    <strong class="text-primary fs-6">{{ $days + 0 }}</strong> 
                                    <span class="ms-1 text-muted">{{ __('days') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif(isset($balance))
                    <div class="mb-2">
                        <strong>{{ __('Remaining Previous Year Leave:') }}</strong>
                        <span class="text-danger">{{ $balance->previous_year }}</span>
                        {{ __('day(s)') }}
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('Remaining Total Annual Leave:') }}</strong>
                        <span class="text-danger">{{ $balance->total_anunal_leave }}</span>
                        {{ __('day(s)') }}
                    </div>
                @else
                    <div class="alert alert-warning py-2 small">
                        <i class="fa fa-info-circle me-1"></i>
                        {{ __('No leave balance found. You may need to request an allocation.') }} 
                    </div>
                @endif
            </div>

            <!-- Leave Type -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave Type') }}</x-form.label>
                    <select name="leave_type_id" class="form-control" required>
                        <option value="">{{ __('--- Select Leave Type ---') }}</option>
                        @foreach ($leavetypes as $type)
                            <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Half‑Day checkbox -->
            <div class="col-md-6">
                <input type="hidden" name="half_day" value="0">
                <div class="input-block mb-3">
                    <x-form.label for="halfDay">{{ __('Half Day Leave') }}</x-form.label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="half_day" id="halfDay" value="1">
                        <label class="form-check-label" for="halfDay">
                            {{ __('Check if you want Half Day Leave') }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Half‑Day time -->
            <div class="col-md-6" id="half-day-time-section" style="display:none;">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Select Half Day Time') }}</x-form.label>
                    <select name="half_day_time" class="form-control">
                        <option value="">{{ __('--- Select half ---') }}</option>
                        <option value="morning">{{ __('Morning') }}</option>
                        <option value="afternoon">{{ __('Afternoon') }}</option>
                    </select>
                </div>
            </div>

            <!-- Start Date -->
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave Start Date') }}</x-form.label>
                    <x-form.input type="date" name="leave_start_date" id="leave_start_date" required />
                </div>
            </div>

            <!-- End Date -->
            <div class="col-md-6" id="leave-end-date-section">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave End Date') }}</x-form.label>
                    <x-form.input type="date" name="leave_end_date" id="leave_end_date" required />
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Reason -->
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Description/Reason') }}</label>
                    <textarea name="request_reason" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <!-- Attachments -->
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Attachment') }}
                        <small class="text-info">{{ __('You can upload multiple files') }}</small>
                    </label>
                    <input type="file" name="attachements[]" class="form-control" multiple>
                </div>
            </div>
        </div>

        <div class="submit-section mb-3">
            <button type="submit" class="btn btn-primary submit-btn">{{ __('Submit') }}</button>
        </div>
    </form>
</div>

<script>
    (function($) {
        function toggleHalfDay() {
            const checked = $('#halfDay').is(':checked');
            if (checked) {
                $('#half-day-time-section').show();
                $('#leave-end-date-section').hide();
                const start = $('#leave_start_date').val();
                if (start) $('#leave_end_date').val(start);
            } else {
                $('#half-day-time-section').hide();
                $('#leave-end-date-section').show();
                $('#leave_end_date').val('');
            }
        }

        $(document).ready(function() {
            $('#halfDay').on('change', toggleHalfDay);
            $('#leave_start_date').on('change', function() {
                if ($('#halfDay').is(':checked')) {
                    $('#leave_end_date').val($(this).val());
                }
            });
        });
    })(jQuery);
</script>
