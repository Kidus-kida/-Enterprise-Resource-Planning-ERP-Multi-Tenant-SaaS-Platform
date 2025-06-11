
<div class="modal-body" >
    <form action="{{ route('folders.update', $folder->id) }}" method="post">
        @csrf
        @method("PUT")
        <x-form.input-block>
            <x-form.label>{{ __('Name') }}</x-form.label>
            <x-form.input type="text" name="name" placeholder="Enter Folder Name" value="{{ $folder->name }}" />
        </x-form.input-block>
        <x-form.input-block>
            <x-form.label>{{ __('Members') }} ({{ count($folderMemberIds) }})</x-form.label>
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-4 mb-2">
                        <label class="form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                name="members[]" 
                                value="{{ $user->id }}"
                                {{ in_array($user->id, $folderMemberIds) ? 'checked' : '' }}
                            >
                            {{ $user->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </x-form.input-block>
     

        {{-- @endif --}}
        <div class="submit-section mb-3">
            <x-form.button class="btn btn-primary submit-btn w-[50%]">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>


