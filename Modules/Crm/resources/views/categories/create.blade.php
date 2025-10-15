<div class="modal-body">
    <form action="{{ route('campaigns.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <x-form.input-block>
            <x-form.label required>{{ __('Title') }}</x-form.label>
            <x-form.input type="text" name="title"/>
        </x-form.input-block>
        <x-form.input-block>
            <x-form.label required>{{ __('Description') }}</x-form.label>
            <x-form.input type="text" name="description"/>
        </x-form.input-block>
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
