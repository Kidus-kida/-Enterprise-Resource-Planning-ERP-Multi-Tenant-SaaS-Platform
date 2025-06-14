{{-- resources/views/leaverequests/create.blade.php --}}
{{-- <div class="modal-header">
    <h5 class="modal-title">{{ __('Add Leave Request') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div> --}}

<div class="modal-body">
    <form action="{{ route('leaverequests.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
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
                <input type="hidden" name="half_day" value="0"> {{-- or "off" --}}
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

            @can('edit-ticket')
                <!-- Start Date -->
                <div class="col-md-6">
                    <div class="input-block mb-3">
                        <x-form.label>{{ __('Leave Start Date') }}</x-form.label>
                        <input class="form-control datepicker" type="text" name="leave_start_date" id="leave_start_date">
                    </div>
                </div>

                <!-- End Date -->
                <div class="col-md-6" id="leave-end-date-section">
                    <div class="input-block mb-3">
                        <x-form.label>{{ __('Leave End Date') }}</x-form.label>
                        <input class="form-control datepicker" type="text" name="leave_end_date" id="leave_end_date">
                    </div>
                </div>
            @endcan
        </div>

        <div class="row">
            <!-- Reason -->
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Description/Reason') }}</label>
                    <x-form.ckeditor name="request_reason" id="editor" />
                </div>
            </div>

            <!-- Attachments -->
            <div class="input-block mb-3">
                <label class="col-form-label">{{ __('Attachment') }}
                    <small class="text-info">{{ __('You can upload multiple files') }}</small>
                </label>
                <x-form.input type="file" name="attachements[]" multiple />
            </div>
        </div>

        <div class="submit-section my-2">
            <x-form.button class="btn btn-primary">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
