<div class="modal-body">
    <form action="{{ route('employees.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('First Name') }}</x-form.label>
                    <x-form.input type="text" name="firstname" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Middle Name') }}</x-form.label>
                    <x-form.input type="text" name="middlename" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Last Name') }}</x-form.label>
                    <x-form.input type="text" name="lastname" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('UserName') }}</x-form.label>
                    <x-form.input type="text" name="username" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Email') }}</x-form.label>
                    <x-form.input type="email" name="email" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <label>{{ __('Phone Number') }}</label>
                    <x-form.phone type="text" name="phone" />
                </div>
            </div>
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>
                        {{ __('Password') }}
                    </x-form.label>
                    <x-form.input type="password" name="password" />
                </x-form.input-block>
            </div>
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>
                        {{ __('Confirm Password') }}
                    </x-form.label>
                    <x-form.input type="password" name="password_confirmation" />
                </x-form.input-block>
            </div>
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>
                        {{ __('Department') }}
                    </x-form.label>
                    <select name="department" id="department" class="select">
                        @if (!empty($departments))
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </x-form.input-block>
            </div>

            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Designation') }}</x-form.label>
                    <select name="designation" id="designation" class="select">
                        @if (!empty($designations))
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </x-form.input-block>
            </div>
            
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Manager') }}</x-form.label>
                    <select name="manager" id="manager" class="select">
                        <option value="">{{ __('Select Manager') }}</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Company') }}</x-form.label>
                    <select name="company" id="company" class="select">
                         <option value="">{{ __('Select Company') }}</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>
            <div class="col-sm-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Job Position') }}</x-form.label>
                    <select name="job_position" id="job_position" class="select">
                        <option value="">{{ __('Select Job Position') }}</option>
                        @foreach ($jobPositions as $jobPosition)
                            <option value="{{ $jobPosition->id }}">{{ $jobPosition->name }}</option>
                        @endforeach
                        <option value="add_new" data-icon="fa fa-plus-circle" class="text-primary fw-bold">+ {{ __('New Job Position') }}</option>
                    </select>
                </x-form.input-block>
            </div>
            <div class="col-sm-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Job Title') }}</x-form.label>
                    <x-form.input type="text" name="job_title" />
                </div>
            </div>
            <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Address') }}</x-form.label>
                    <x-form.input type="text" name="address" />
                </div>
            </div>
            <div class="col">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Avatar') }}</label>
                    <x-form.input type="file" name="avatar" />
                </div>
            </div>
            <div class="col">
                <div class="status-toggle">
                    <x-form.label>{{ __('Status') }}</x-form.label>
                    <x-form.input type="checkbox" id="status" class="check" name="status" />
                    <label for="status" class="checktoggle">checkbox</label>
                </div>
            </div>
        </div>
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>

@include('pages.employees.modals.create-job-position')

<script>
    $(document).ready(function() {
        // Move modal to body to avoid nesting issues
        $('#add_job_position_modal').appendTo('body');
        
        // Re-initialize select2 for the moved modal if needed, or rely on global init.
        // Usually, moving elements might break event bindings or plugins if they were already initialized.
        // But here we are just appending raw HTML? No, it's rendered.
        
        $('#job_position').on('change', function() {
            if ($(this).val() === 'add_new') {
                $(this).val('').trigger('change');
                $('#add_job_position_modal').modal('show');
            }
        });

        $('#add_job_position_form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('.submit-btn');
            btn.prop('disabled', true);
            
            $.ajax({
                url: "{{ route('job-positions.store') }}",
                method: "POST",
                data: form.serialize(),
                success: function(response) {
                    btn.prop('disabled', false);
                    if(response.success) {
                        $('#add_job_position_modal').modal('hide');
                        // Add new option
                        var newOption = new Option(response.job_position.name, response.job_position.id, true, true);
                        // Append before the last option (Add New)
                        var addNewOption = $('#job_position option[value="add_new"]');
                        if(addNewOption.length > 0) {
                            addNewOption.before(newOption);
                        } else {
                            $('#job_position').append(newOption);
                        }
                        $('#job_position').val(response.job_position.id).trigger('change');
                        
                        // Reset form
                        form[0].reset();
                        // Reset select2s in the modal if any
                        form.find('select').val('').trigger('change');
                        
                        // Show success message (using notify if available or alert)
                        // toastr.success('Job Position Added');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    console.error(xhr.responseText);
                    // Handle validation errors if needed
                }
            });
        });
    });
</script>
