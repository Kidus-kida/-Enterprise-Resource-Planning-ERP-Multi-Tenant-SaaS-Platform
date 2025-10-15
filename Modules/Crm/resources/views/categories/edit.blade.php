<div class="modal-body">
    <form action="{{ route('campaigns.update', $category->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method("PUT")
        <x-form.input-block>
            <x-form.label>{{ __('Title') }}</x-form.label>
            <x-form.input type="text" name="title" placeholder="Enter title" value="{{ $category->title }}" />
        </x-form.input-block>
        <x-form.input-block>
            <x-form.label>{{ __('Description') }}</x-form.label>
            <x-form.input type="text" name="description" placeholder="Enter description" value="{{ $category->description }}" />
        </x-form.input-block>
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
