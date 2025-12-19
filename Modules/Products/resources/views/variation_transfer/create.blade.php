<div class="modal-dialog" role="document" style="width: 55%;">
    <div class="modal-content">

        <form action="{{ route('products.variation-transfer.store') }}" method="post" id="variation_transfer_add_form"
            class="form-horizontal">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">@lang('lang_v1.variation_transfer')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="date">@lang('lang_v1.date'):*</label>
                            <input type="text" name="date" id="date" class="form-control date" required
                                placeholder="@lang('lang_v1.date')">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="from_location">@lang('lang_v1.from_location'):*</label>
                            <select name="from_location" id="from_location" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="from_store">@lang('lang_v1.from_store'):*</label>
                            <select name="from_store" id="from_store" class="form-control select2" style="width: 100%;"
                                required>
                                <option value="">@lang('messages.please_select')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="to_location">@lang('lang_v1.to_location'):*</label>
                            <select name="to_location" id="to_location" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($business_locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="to_store">@lang('lang_v1.to_store'):*</label>
                            <select name="to_store" id="to_store" class="form-control select2" style="width: 100%;"
                                required>
                                <option value="">@lang('messages.please_select')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="category_id">@lang('lang_v1.category'):*</label>
                            <select name="category_id" id="category_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="sub_category_id">@lang('lang_v1.sub_category'):*</label>
                            <select name="sub_category_id" id="sub_category_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($sub_categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="from_variation_id">@lang('lang_v1.product_from'):*</label>
                            <select name="from_variation_id" id="from_variation_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($variations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="to_variation_id">@lang('lang_v1.product_to'):*</label>
                            <select name="to_variation_id" id="to_variation_id" class="form-control select2"
                                style="width: 100%;" required>
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($variations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="qty">@lang('lang_v1.qty'):*</label>
                            <input type="text" name="qty" id="qty" class="form-control"
                                placeholder="@lang('lang_v1.qty')">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="unit_cost">@lang('lang_v1.unit_cost'):*</label>
                            <input type="text" name="unit_cost" id="unit_cost" class="form-control"
                                placeholder="@lang('lang_v1.unit_cost')">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label" for="total_cost">@lang('lang_v1.total_cost'):*</label>
                            <input type="text" name="total_cost" id="total_cost" class="form-control"
                                placeholder="@lang('lang_v1.total_cost')">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('messages.close')</button>
            </div>

        </form>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->


<script>
    $('.date').datepicker('setDate', new Date());
    $('.select2').each(function () {
        $(this).select2({
            dropdownParent: $(this).parent()
        });
    });

    $('#from_location').change(function () {
        let check_store_not = null;
        $.ajax({
            method: 'get',
            url: '/products/variation-transfer/get-store/' + $(this).val(),
            data: { check_store_not: check_store_not },
            success: function (result) {

                $('#from_store').empty();
                $('#from_store').append(`<option value="">Please Select</option>`);
                $.each(result, function (i, location) {
                    $('#from_store').append(`<option value= "` + location.id + `">` + location.name + `</option>`);
                });
                $('#from_store option:eq(1)').attr('selected', true).change();
            },
        });
    });
    $('#to_location').change(function () {
        let check_store_not = null;
        $.ajax({
            method: 'get',
            url: '/products/variation-transfer/get-store/' + $(this).val(),
            data: { check_store_not: check_store_not },
            success: function (result) {

                $('#to_store').empty();
                $('#to_store').append(`<option value="">Please Select</option>`);
                $.each(result, function (i, location) {
                    $('#to_store').append(`<option value= "` + location.id + `">` + location.name + `</option>`);
                });
                $('#to_store option:eq(1)').attr('selected', true).change();
            },
        });
    });

    $('#category_id, #sub_category_id').change(function () {
        var this_id = $(this).attr('id');
        var cat = $('#category_id').val();
        var sub_cat = $('#sub_category_id').val();
        $.ajax({
            method: 'POST',
            url: '/products/get_sub_categories',
            dataType: 'html',
            data: { cat_id: cat, _token: "{{ csrf_token() }}" },
            success: function (result) {
                if (result) {
                    if (this_id !== 'sub_category_id') {
                        $('#sub_category_id').html(result);
                    }
                }
            },
        });
        $.ajax({
            method: 'GET',
            url: '/products/variation-transfer/get-variation-by-category',
            dataType: 'html',
            data: { cat_id: cat, sub_cat_id: sub_cat },
            success: function (result) {
                if (result) {
                    $('#from_variation_id').html(result);
                }
            },
        });
    });

    $('#qty, #unit_cost').change(function () {
        var qty = __read_number($('#qty'));
        var unit_cost = __read_number($('#unit_cost'));

        if (qty > 0 && unit_cost > 0) {
            total_cost = qty * unit_cost;
            __write_number($('#total_cost'), total_cost);
        }
    })

    $('#from_variation_id').change(function () {
        var variation_id = $(this).val();

        $.ajax({
            method: 'get',
            url: '/products/variation-transfer/get-variation-of-product/' + variation_id,
            data: {},
            success: function (result) {
                $('#to_variation_id').html(result);
            },
        });
    })
</script>