<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title">@lang('report.filters')</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_location_from">@lang('lang_v1.from_location'):</label>
                    <select name="from_location" id="filter_location_from" class="form-control select2"
                        style="width:100%">
                        <option value="">@lang('messages.all')</option>
                        @foreach($business_locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_location_to">@lang('lang_v1.to_location'):</label>
                    <select name="to_location" id="filter_location_to" class="form-control select2" style="width:100%">
                        <option value="">@lang('messages.all')</option>
                        @foreach($business_locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_from_store">@lang('lang_v1.from_store'):</label>
                    <select name="from_store" id="filter_from_store" class="form-control select2" style="width:100%">
                        <option value="">@lang('messages.all')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_to_store">@lang('lang_v1.to_store'):</label>
                    <select name="to_store" id="filter_to_store" class="form-control select2" style="width:100%">
                        <option value="">@lang('messages.all')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_category_id">@lang('lang_v1.category'):</label>
                    <select name="category_id" id="filter_category_id" class="form-control select2" style="width:100%">
                        <option value="">@lang('messages.all')</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_sub_category_id">@lang('lang_v1.sub_category'):</label>
                    <select name="sub_category_id" id="filter_sub_category_id" class="form-control select2"
                        style="width:100%">
                        <option value="">@lang('messages.all')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_from_variation_id">@lang('lang_v1.product_from'):</label>
                    <select name="from_variation_id" id="filter_from_variation_id" class="form-control select2"
                        style="width:100%">
                        <option value="">@lang('messages.all')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="filter_to_variation_id">@lang('lang_v1.product_to'):</label>
                    <select name="to_variation_id" id="filter_to_variation_id" class="form-control select2"
                        style="width:100%">
                        <option value="">@lang('messages.all')</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="form_date_range">@lang('report.date_range'):</label>
                    <input type="text" id="form_date_range" class="form-control" readonly
                        placeholder="@lang('report.date_range')">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">@lang('lang_v1.all_variation_transfer')</h5>
        @can('product.create')
            <button type="button" class="btn btn-primary btn-modal"
                data-href="{{route('products.variation-transfer.create')}}" data-container=".variation_modal">
                <i class="fa fa-plus"></i> @lang('messages.add')
            </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="variation_transfer_table" style="width:100%">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('lang_v1.date')</th>
                        <th>@lang('lang_v1.from_location')</th>
                        <th>@lang('lang_v1.to_location')</th>
                        <th>@lang('lang_v1.from_store')</th>
                        <th>@lang('lang_v1.to_store')</th>
                        <th>@lang('lang_v1.category')</th>
                        <th>@lang('lang_v1.sub_category')</th>
                        <th>@lang('lang_v1.product_from')</th>
                        <th>@lang('lang_v1.product_to')</th>
                        <th>@lang('lang_v1.qty')</th>
                        <th>@lang('lang_v1.unit_cost')</th>
                        <th>@lang('lang_v1.total_cost')</th>
                        <th>@lang('lang_v1.added_by')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>