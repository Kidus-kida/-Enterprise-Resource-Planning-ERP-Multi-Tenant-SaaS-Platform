<div id="add_job_position_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Create Job Position') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add_job_position_form">
                    @csrf
                    <div class="input-block mb-3">
                        <label>{{ __('Job Position Name') }} <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="name" required>
                    </div>
                    <div class="input-block mb-3">
                        <label>{{ __('Company') }} <span class="text-danger">*</span></label>
                        <select class="select form-control" name="company_id" required>
                            <option value="">{{ __('Select Company') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-block mb-3">
                        <label>{{ __('Department') }} <span class="text-danger">*</span></label>
                        <select class="select form-control" name="department_id" required>
                            <option value="">{{ __('Select Department') }}</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
