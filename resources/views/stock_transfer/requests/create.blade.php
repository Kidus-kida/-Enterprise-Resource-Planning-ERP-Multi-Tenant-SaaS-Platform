<form action="{{ route('stock-transfers-request.store') }}" method="POST" id="add_transfer_request_form">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_request_location">@lang('Request Location'):</label>
                    <select name="request_location" id="add_request_location" class="form-control select2"
                        style="width: 100%;" required>
                        <option value="">@lang('please select')</option>
                        @foreach($business_locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_request_to_location">@lang('Request To Location'):</label>
                    <select name="request_to_location" id="add_request_to_location" class="form-control select2"
                        style="width: 100%;" required>
                        <option value="">@lang('please select')</option>
                        @foreach($business_locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_store_id">@lang('To Store'):</label>
                    <select name="store_id" id="add_store_id" class="form-control select2" style="width: 100%;"
                        required>
                        <option value="">@lang('please select')</option>
                        @foreach($tostore as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_category_id">@lang('Category'):</label>
                    <select name="category_id" id="add_category_id" class="form-control select2" style="width: 100%;">
                        <option value="">@lang('please select')</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_sub_category_id">@lang('Sub Category'):</label>
                    <select name="sub_category_id" id="add_sub_category_id" class="form-control select2"
                        style="width: 100%;" required>
                        <option value="">@lang('please select')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_product_id">@lang('Products'):</label>
                    <select name="product_id" id="add_product_id" class="form-control select2" style="width: 100%;"
                        required>
                        <option value="">@lang('please select')</option>
                        @foreach($products as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_qty">@lang('Qty'):</label>
                    <input type="text" name="qty" id="add_qty" class="form-control" placeholder="@lang('Qty')" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label" for="add_delivery_need_on">@lang('Delivery Need On'):</label>
                    <input type="date" name="delivery_need_on" id="add_delivery_need_on" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang('Save')</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('#add_transfer_request_form .select2').select2({
            dropdownParent: $('#generalModalPopup')
        });

        $('#add_category_id').change(function () {
            var cat = $(this).val();
            $.ajax({
                method: 'POST',
                url: '/products/get_sub_categories',
                data: { cat_id: cat },
                success: function (result) {
                    $('#add_sub_category_id').html(result);
                },
            });
        });

        $('#add_sub_category_id').change(function () {
            var cat = $('#add_category_id').val();
            var sub_cat = $(this).val();
            $.ajax({
                method: 'POST',
                url: '/products/get_product_category_wise',
                data: { cat_id: cat, sub_cat_id: sub_cat },
                success: function (result) {
                    $('#add_product_id').html(result);
                },
            });
        });

        // Set default date to today
        var today = new Date().toISOString().split('T')[0];
        $('#add_delivery_need_on').val(today);
    });
</script>