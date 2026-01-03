        <form id="typeForm" method="POST"
            action="{{ isset($type) ? route('account-types.update', $type->id) : route('account-types.store') }}">
            @csrf
            @if (isset($type))
                @method('PUT')
            @endif

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $type->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Parent Account Type') }}</label>
                    <select name="parent_account_type_id" id="parent_type_select" class="form-control" style="width: 100%">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($parent_types as $id => $name)
                            <option value="{{ $id }}"
                                {{ isset($type) && $type->parent_account_type_id == $id ? 'selected' : '' }}>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ $type->description ?? '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit"
                    class="btn btn-primary">{{ isset($type) ? __('Update') : __('Create') }}</button>
            </div>
        </form>

        <script>
            $(document).ready(function() {
                $('#parent_type_select').select2({
                    dropdownParent: $('#generalModalPopup'),
                    width: '100%'
                });
            });
        </script>


