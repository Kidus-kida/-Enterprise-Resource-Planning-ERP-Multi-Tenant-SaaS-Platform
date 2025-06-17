<div class="modal-body">
    <form action="{{ route('leavetypes.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Leave Type Name') }}</x-form.label>
                    <x-form.input type="text" name="type_name" required />
                </div>
            </div>

            <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Maximum Date Allowed ') }}</x-form.label>
                    <x-form.input type="number" name="max_date_allowed" required />
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-12">
                <div class="input-block mb-3">
                    <label class="col-form-label">{{ __('Description') }}</label>
                    <x-form.ckeditor name="description" id="editor"></x-form.ckeditor>
                </div>
            </div>

        </div>
        <div class="submit-section my-2">
            <x-form.button class="btn btn-primary submit-btn" type="submit">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
