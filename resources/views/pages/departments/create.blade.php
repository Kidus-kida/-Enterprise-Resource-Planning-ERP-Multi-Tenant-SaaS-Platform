<div class="modal-body">
    <form action="{{ route('departments.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Name') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="text" name="name" placeholder="E.g. Sales" required />
                </x-form.input-block>
            </div>
            <div class="col-md-6">
                 <x-form.input-block>
                    <x-form.label>{{ __('Company') }}</x-form.label>
                    <x-form.input type="text" name="company_name" placeholder="Company Name" />
                </x-form.input-block>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Manager') }}</x-form.label>
                    <select name="manager_id" class="form-control select">
                        <option value="">{{ __('Select Manager') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->fullname }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>
            <div class="col-md-6">
                <x-form.input-block>
                    <x-form.label>{{ __('Parent Department') }}</x-form.label>
                    <select name="parent_id" class="form-control select">
                        <option value="">{{ __('Select Parent Department') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->hierarchical_name }}</option>
                        @endforeach
                    </select>
                </x-form.input-block>
            </div>
        </div>
        
        <x-form.input-block>
            <x-form.label>{{ __('Color') }}</x-form.label>
            <input type="color" name="color" class="form-control form-control-color" value="#007bff" title="Choose your color">
        </x-form.input-block>
        
        <x-form.input-block>
            <x-form.label>{{ __('Description') }}</x-form.label>
            <x-form.textarea name="description"></x-form.textarea>
        </x-form.input-block>

        <input type="hidden" name="location" value=""> <!-- Handled by company_name logic in controller -->
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.select').select2({
            dropdownParent: $('#generalModalPopup'),
            width: '100%'
        });
    });
</script>
