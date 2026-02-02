<div class="modal-body">
    <form action="{{ route('leave.config.public-holidays.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- Basic Information Section --}}
            <div class="col-12">
                <h5 class="mb-3">{{ __('Basic Information') }}</h5>
            </div>
            
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Holiday Name') }}</x-form.label>
                    <x-form.input type="text" name="name" placeholder="Christmas Day" required />
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Color') }}</x-form.label>
                    <select class="select2 form-control" name="color" required>
                        <option value="">{{ __('Select Color') }}</option>
                        @foreach (\App\Enums\CalendarColors::cases() as $item)
                            <option value="{{ $item->value }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Start Date') }}</x-form.label>
                    <div class="cal-icon">
                        <x-form.input type="text" class="datepicker" name="startDate" required />
                    </div>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('End Date') }}</x-form.label>
                    <div class="cal-icon">
                        <x-form.input type="text" class="datepicker" name="endDate" required />
                    </div>
                </x-form.input-block>
            </div>
            
            <div class="col-12">
                <x-form.input-block>
                    <x-form.label>{{ __('Description') }}</x-form.label>
                    <x-form.textarea name="description" rows="2"></x-form.textarea>
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
                        <option value="full_day">{{ __('Full Day') }}</option>
                        <option value="half_day">{{ __('Half Day') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Will this holiday last a full day or half day?') }}</small>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Weekend Adjustment') }}</x-form.label>
                    <select class="select2 form-control" name="weekend_adjustment" required>
                        <option value="none">{{ __('No Adjustment') }}</option>
                        <option value="next_monday">{{ __('Move to Next Monday') }}</option>
                        <option value="previous_friday">{{ __('Move to Previous Friday') }}</option>
                    </select>
                    <small class="text-muted">{{ __('What happens if holiday falls on weekend?') }}</small>
                </x-form.input-block>
            </div>

            <div class="col-md-6">
                <div class="status-toggle mt-3">
                    <x-form.label>{{ __('Repeat Annually?') }}</x-form.label>
                    <x-form.input type="checkbox" id="is_annual" class="check" name="is_annual" value="1" />
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
                    <x-form.input type="checkbox" id="exclude_from_leave" class="check" name="exclude_from_leave" value="1" checked />
                    <label for="exclude_from_leave" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Should this day be excluded when counting leave days?') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Is Paid Holiday') }}</x-form.label>
                    <x-form.input type="checkbox" id="is_paid" class="check" name="is_paid" value="1" checked />
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
                    <x-form.input type="checkbox" id="block_leave_requests" class="check" name="block_leave_requests" value="1" />
                    <label for="block_leave_requests" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Prevent employees from requesting leave on this day?') }}</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="status-toggle">
                    <x-form.label>{{ __('Allow Attendance Exception') }}</x-form.label>
                    <x-form.input type="checkbox" id="allow_attendance_exception" class="check" name="allow_attendance_exception" value="1" />
                    <label for="allow_attendance_exception" class="checktoggle">checkbox</label>
                    <small class="text-muted d-block">{{ __('Allow critical staff to work if needed?') }}</small>
                </div>
            </div>
        </div>

        <div class="submit-section mt-4">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Create Holiday') }}</x-form.button>
        </div>
    </form>
</div>
