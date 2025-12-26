<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form action="{{ route('stock-transfers-request.update', $transfer_request->id) }}" method="POST"
            id="edit_transfer_request_form">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h4 class="modal-title">@lang('lang_v1.edit_request')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"
                                for="edit_request_location">@lang('lang_v1.request_location'):</label>
                            <select name="request_location" id="edit_request_location" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->request_location == $id) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"
                                for="edit_request_to_location">@lang('lang_v1.request_to_location'):</label>
                            <select name="request_to_location" id="edit_request_to_location"
                                class="form-control select2" style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->request_to_location == $id) selected
                                    @endif>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="edit_store_id">@lang('lang_v1.tostore'):</label>
                            <select name="store_id" id="edit_store_id" class="form-control select2" style="width: 100%;"
                                required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($tostore as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->store_id == $id) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="edit_category_id">@lang('lang_v1.category'):</label>
                            <select name="category_id" id="edit_category_id" class="form-control select2"
                                style="width: 100%;">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->category_id == $id) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="edit_sub_category_id">@lang('lang_v1.sub_category'):</label>
                            <select name="sub_category_id" id="edit_sub_category_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->sub_category_id == $id) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="edit_product_id">@lang('lang_v1.products'):</label>
                            <select name="product_id" id="edit_product_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" @if($transfer_request->product_id == $id) selected @endif>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="edit_qty">@lang('lang_v1.qty'):</label>
                            <input type="text" name="qty" id="edit_qty" class="form-control"
                                value="{{ $transfer_request->qty }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label"
                                for="edit_delivery_need_on">@lang('lang_v1.delivery_need_on'):</label>
                            <input type="date" name="delivery_need_on" id="edit_delivery_need_on" class="form-control"
                                value="{{ $transfer_request->delivery_need_on }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#edit_transfer_request_form .select2').select2({
            dropdownParent: $('#edit_transfer_request_form')
        });

        $('#edit_category_id').change(function () {
            var cat = $(this).val();
            $.ajax({
                method: 'POST',
                url: '/products/get_sub_categories',
                data: { cat_id: cat },
                success: function (result) {
                    $('#edit_sub_category_id').html(result);
                },
            });
        });

        $('#edit_sub_category_id').change(function () {
            var cat = $('#edit_category_id').val();
            var sub_cat = $(this).val();
            $.ajax({
                method: 'POST',
                url: '/products/get_product_category_wise',
                data: { cat_id: cat, sub_cat_id: sub_cat },
                success: function (result) {
                    $('#edit_product_id').html(result);
                },
            });
        });
    });
</script>