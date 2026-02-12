<div class="modal-body">
    <form action="{{ route('leavetypes.update', $leaveType->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave Type Name') }}</x-form.label>
                    <x-form.input type="text" name="type_name" value="{{ $leaveType->type_name }}" required />
                </div>
            </div>

            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Maximum Date Allowed ') }}</x-form.label>
                    <x-form.input type="number" name="max_date_allowed" value="{{ $leaveType->max_date_allowed }}"
                        required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Accrual Plan') }}</x-form.label>
                    <select name="default_accrual_plan_id" class="form-select select">
                        <option value="">{{ __('None (No Accrual)') }}</option>
                        @foreach($accrualPlans as $plan)
                            <option value="{{ $plan->id }}" {{ $leaveType->default_accrual_plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave Interval') }}</x-form.label>
                    <x-form.input type="text" name="leave_allowed_interval"
                        value="{{ $leaveType->leave_allowed_interval }}" placeholder="e.g. monthly" />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Description') }}</label>
                    <x-form.ckeditor name="description" id="editor">{{ $leaveType->description }}</x-form.ckeditor>
                </div>
            </div>
        </div>

        <div class="submit-section my-2">
            <x-form.button class="btn btn-primary submit-btn" type="submit">{{ __('Update') }}</x-form.button>
        </div>
    </form>
</div>