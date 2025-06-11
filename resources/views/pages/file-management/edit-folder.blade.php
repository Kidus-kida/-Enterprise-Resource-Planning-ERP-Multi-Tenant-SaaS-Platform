<div class="modal-body">
    <form action="{{ route('folders.update', $folder->id) }}" method="post">
        @csrf
        @method("PUT")
        <x-form.input-block>
            <x-form.label>{{ __('Name') }}</x-form.label>
            <x-form.input type="text" name="name" placeholder="Enter Fodler Name" value="{{ $folder->name }}" />
        </x-form.input-block>
        
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
