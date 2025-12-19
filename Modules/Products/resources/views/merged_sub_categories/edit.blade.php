<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@lang('Edit Merged Sub Category')</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <input type="hidden" value="{{$merge->id}}" name="merge_id" id="merge_id">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="date_and_time">@lang('Date & Time'):*</label>
                        <input class="form-control" id="date_and_time" required placeholder="@lang('Date & Time')"
                            name="date_and_time" type="text"
                            value="{{ date('m/d/Y', strtotime($merge->date_and_time)) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="merged_sub_category_name">@lang('Merged Sub Category Name'):*</label>
                        <input class="form-control" id="merged_sub_category_name" required
                            placeholder="@lang('Merged Sub Category Name')" name="merged_sub_category_name" type="text"
                            value="{{ $merge->merged_sub_category_name }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="category">@lang('Category'):*</label>
                        <select class="form-control" id="category" required name="category">
                            <option value="">@lang('Please Select')</option>
                            @foreach($categories as $key => $value)
                                <option value="{{ $key }}" @if($merge->category_id == $key) selected @endif>{{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="sub_categories">@lang('Sub Categories'):*</label>
                        <select class="form-control select2" id="sub_categories" multiple style="width: 100%;" required
                            name="sub_categories[]">
                            @foreach($sub_categories as $key => $value)
                                <option value="{{ $key }}" @if(in_array($key, $merge->sub_categories ?? [])) selected
                                @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="status">@lang('Status'):*</label>
                        <select class="form-control" id="status" required name="status">
                            <option value="">@lang('Please Select')</option>
                            <option value="1" @if($merge->status == 1) selected @endif>Active</option>
                            <option value="0" @if($merge->status == 0) selected @endif>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary edit_merged_sub_category">@lang('Update')</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
        </div>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    if ($.fn.datepicker) {
        $('#date_and_time').datepicker("setDate", new Date("{{ date('m/d/Y', strtotime($merge->date_and_time)) }}"));
    }
    if ($.fn.select2) {
        $('#sub_categories').select2();
    }

    $('#category').change(function () {
        var categoryId = $(this).val();
        if (categoryId) {
            $.ajax({
                contentType: 'html',
                method: 'get',
                url: "{{ url('products/merged-sub-categories/get-sub-categories') }}/" + categoryId,
                data: {},
                success: function (result) {
                    $('#sub_categories').empty().append(result);
                },
                error: function (xhr) {
                    console.error('Error loading sub-categories:', xhr);
                }
            });
        } else {
            $('#sub_categories').empty();
        }
    });
</script>