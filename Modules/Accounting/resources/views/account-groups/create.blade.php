        <form id="groupForm" method="POST"
            action="{{ isset($group) ? route('account-groups.update', $group->id) : route('account-groups.store') }}">
            @csrf
            @if (isset($group))
                @method('PUT')
            @endif

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $group->name ?? '' }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Account Type') }} <span class="text-danger">*</span></label>
                    <select name="account_type_id" id="account_type_select" class="form-control" required style="width: 100%">
                        <option value="">{{ __('Select Account Type') }}</option>
                        @foreach ($account_types as $id => $name)
                            <option value="{{ $id }}"
                                {{ isset($group) && $group->account_type_id == $id ? 'selected' : '' }}>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3">{{ $group->description ?? '' }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit"
                    class="btn btn-primary">{{ isset($group) ? __('Update') : __('Create') }}</button>
            </div>
        </form>
        
        <script>
            $(document).ready(function() {
                $('#account_type_select').select2({
                    dropdownParent: $('#generalModalPopup'),
                    width: '100%'
                });
            });
        </script>
