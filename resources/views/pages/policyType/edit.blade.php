<div class="modal-body">
    <form action="{{ route('policy-type.update', $policyType->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-block mb-3">
                            <x-form.label>{{ __('Name') }}</x-form.label>
                            <x-form.input type="text" name="name" value="{{ $policyType->name }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
