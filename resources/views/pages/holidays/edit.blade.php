<div class="modal-body">
    <form action="{{ route('leave.config.public-holidays.update', ['public_holiday' => $holiday->id]) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method("PUT")
        <div class="row">
            {{-- Basic Information Section --}}
            <div class="col-12">
                <h5 class="mb-3">{{ __('Basic Information') }}</h5>
            </div>
            
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Holiday Name') }}</x-form.label>
                    <x-form.input type="text" name="name" placeholder="Christmas Day" value="{{ $holiday->name ?? old('name') }}" required />
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Color') }}</x-form.label>
                    <select class="select2 form-control" name="color" required>
                        <option value="">{{ __('Select Color') }}</option>
                        @foreach (\App\Enums\CalendarColors::cases() as $item)
                            <option value="{{ $item->value }}" {{ (!empty($holiday->color) && ($holiday->color->value == $item->value)) ? 'selected': ''}}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Start Date') }}</x-form.label>
                    <div class="cal-icon">
                        <x-form.input type="text" class="datepicker" name="startDate" value="{{ $holiday->startDate ?? old('startDate') }}" required />
                    </div>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('End Date') }}</x-form.label>
                    <div class="cal-icon">
                        <x-form.input type="text" class="datepicker" name="endDate" value="{{ $holiday->endDate ?? old('endDate') }}" required />
                    </div>
                </x-form.input-block>
            </div>
            
            <div class="col-12">
                <x-form.input-block>
                    <x-form.label>{{ __('Description') }}</x-form.label>
                    <x-form.textarea name="description" rows="2">{{ $holiday->description }}</x-form.textarea>
                </x-form.input-block>
            </div>

            {{-- Duration & Recurrence Section --}}
            <div class="col-12 mt-3">
                <h5 class="mb-3">{{ __('Duration & Recurrence') }}</h5>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Duration') }}</x-form.label>
                    <select class="select2 form-control" name="duration" required>
                        <option value="full_day" {{ ($holiday->duration ?? 'full_day') == 'full_day' ? 'selected' : '' }}>{{ __('Full Day') }}</option>
                        <option value="half_day" {{ ($holiday->duration ?? '') == 'half_day' ? 'selected' : '' }}>{{ __('Half Day') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Will this holiday last a full day or half day?') }}</small>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Weekend Adjustment') }}</x-form.label>
                    <select class="select2 form-control" name="weekend_adjustment" required>
                        <option value="none" {{ ($holiday->weekend_adjustment ?? 'none') == 'none' ? 'selected' : '' }}>{{ __('No Adjustment') }}</option>
                        <option value="next_monday" {{ ($holiday->weekend_adjustment ?? '') == 'next_monday' ? 'selected' : '' }}>{{ __('Move to Next Monday') }}</option>
                        <option value="previous_friday" {{ ($holiday->weekend_adjustment ?? '') == 'previous_friday' ? 'selected' : '' }}>{{ __('Move to Previous Friday') }}</option>
                    </select>
                    <small class="text-muted">{{ __('What happens if holiday falls on weekend?') }}</small>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <div class="status-toggle mt-3">
                    <x-form.label>{{ __('Repeat Annually?') }}</x-form.label>
                    <input type="checkbox" class="form-control check" id="is_annual" name="is_annual" value="1" {{ (!empty($holiday->is_annual)) ? 'checked': '' }}>
                    <label for="is_annual" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Will this holiday occur every year?') }}</small>
                </div>
            </div>

            {{-- Leave & Payroll Settings Section --}}
            <div class="col-12 mt-3">
                <h5 class="mb-3">{{ __('Leave & Payroll Settings') }}</h5>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Exclude from Leave Calculations') }}</x-form.label>
                    <input type="checkbox" class="form-control check" id="exclude_from_leave" name="exclude_from_leave" value="1" {{ ($holiday->exclude_from_leave ?? true) ? 'checked': '' }}>
                    <label for="exclude_from_leave" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Should this day be excluded when counting leave days?') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Is Paid Holiday') }}</x-form.label>
                    <input type="checkbox" class="form-control check" id="is_paid" name="is_paid" value="1" {{ ($holiday->is_paid ?? true) ? 'checked': '' }}>
                    <label for="is_paid" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Do employees get paid for this holiday?') }}</small>
                </div>
            </div>

            {{-- Restrictions Section --}}
            <div class="col-12 mt-3">
                <h5 class="mb-3">{{ __('Restrictions (Optional)') }}</h5>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Block Leave Requests') }}</x-form.label>
                    <input type="checkbox" class="form-control check" id="block_leave_requests" name="block_leave_requests" value="1" {{ ($holiday->block_leave_requests ?? false) ? 'checked': '' }}>
                    <label for="block_leave_requests" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Prevent employees from requesting leave on this day?') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Allow Attendance Exception') }}</x-form.label>
                    <input type="checkbox" class="form-control check" id="allow_attendance_exception" name="allow_attendance_exception" value="1" {{ ($holiday->allow_attendance_exception ?? false) ?'checked': '' }}>
                    <label for="allow_attendance_exception" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Allow critical staff to work if needed?') }}</small>
                </div>
            </div>
        </div>

        <div class="submit-section mt-4">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Update Holiday') }}</x-form.button>
        </div>
    </form>
</div>
