<div class="row">
     <h4 class="card-title text-primary">product Settings</h4>
        <p class="text-muted">Configure product default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">sku_prefix</label>
            <input type="text" name="sku_prefix" class="form-control text-uppercase" value="{{ optional($business)->sku_prefix?? '' }}">
        </div>
    </div>

    @if (!config('constants.disable_expiry'))
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label class="form-label">enable_product_expiry</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <input type="checkbox" name="enable_product_expiry" value="1"
                            {{ optional($business)->enable_product_expiry == 1 ? 'checked' : '' }}>
                    </span>
                    <select class="form-control select" name="expiry_type"
                        {{ optional($business)->enable_product_expiry == 1 ? '' : 'disabled' }}>
                        <option value="add_expiry" {{ optional($business)->expiry_type == 'add_expiry' ? 'selected' : '' }}>
                            add_expiry</option>
                        <option value="add_manufacturing"
                            {{ optional($business)->expiry_type == 'add_manufacturing' ? 'selected' : '' }}>add_manufacturing_auto_expiry
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-4 {{ optional($business)->enable_product_expiry == 1 ? '' : 'd-none' }}" id="on_expiry_div">
            <div class="form-group mb-3">
                <label class="form-label">on_product_expiry</label>
                <div class="input-group">
                    <select class="form-control select" name="on_product_expiry">
                        <option value="keep_selling"
                            {{ optional($business)->on_product_expiry == 'keep_selling' ? 'selected' : '' }}>keep_selling
                        </option>
                        <option value="stop_selling"
                            {{ optional($business)->on_product_expiry == 'stop_selling' ? 'selected' : '' }}>stop_selling
                        </option>
                    </select>
                    <input type="number" name="stop_selling_before" class="form-control"
                        value="{{ optional($business)->stop_selling_before }}" placeholder="Stop n days before">
                </div>
            </div>
        </div>
    @endif

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_brand">enable_brand</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_brand" id="enable_brand"
                    value="1" {{ optional($business)->enable_brand == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_category">enable_category</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_category" id="enable_category"
                    value="1" {{ optional($business)->enable_category == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4 {{ optional($business)->enable_category == 1 ? '' : 'd-none' }} enable_sub_category">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_sub_category">enable_sub_category</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_sub_category" id="enable_sub_category"
                    value="1" {{ optional($business)->enable_sub_category == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_price_tax">enable_price_tax</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_price_tax" id="enable_price_tax"
                    value="1" {{ optional($business)->enable_price_tax == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">default_unit</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-balance-scale"></i></span>
                <select name="default_unit" class="form-control select">
                    <option value="">Select Unit</option>
                    @foreach (optional($units_dropdown) as $key => $val)
                        <option value="{{ $key }}" {{ optional($business)->default_unit == $key ? 'selected' : '' }}>
                            {{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_sub_units">enable_sub_units</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_sub_units" id="enable_sub_units"
                    value="1" {{ optional($business)->enable_sub_units == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_racks">enable_racks</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_racks" id="enable_racks"
                    value="1" {{ optional($business)->enable_racks == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_row">enable_row</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_row" id="enable_row"
                    value="1" {{ optional($business)->enable_row == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_position">enable_position</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_position" id="enable_position"
                    value="1" {{ optional($business)->enable_position == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="show_avai_qty_in_qr_catalogue">show_avai_qty_in_qr_catalogue</label>
                <input class="form-check-input ms-0" type="checkbox" name="show_avai_qty_in_qr_catalogue"
                    id="show_avai_qty_in_qr_catalogue" value="1"
                    {{ optional($business)->show_avai_qty_in_qr_catalogue == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="show_in_catalogue_page">show_in_catalogue_page</label>
                <input class="form-check-input ms-0" type="checkbox" name="show_in_catalogue_page"
                    id="show_in_catalogue_page" value="1"
                    {{ optional($business)->show_in_catalogue_page == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_product_warranty">enable_product_warranty</label>
                <input class="form-check-input ms-0" type="checkbox" name="common_settings[enable_product_warranty]"
                    id="enable_product_warranty" value="1"
                    {{ optional($common_settings)['enable_product_warranty'] ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <h4>pos_invoive_sale</h4>
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="search_product_settings[enable_code]"
                    value="1" {{ !empty($search_product_settings['enable_code']) ? 'checked' : '' }}>
                <label class="form-check-label">enable_code</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="search_product_settings[enable_rack_number]"
                    value="1" {{ !empty($search_product_settings['enable_rack_number']) ? 'checked' : '' }}>
                <label class="form-check-label">enable_rack_number</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="search_product_settings[enable_qty]"
                    value="1" {{ !empty($search_product_settings['enable_qty']) ? 'checked' : '' }}>
                <label class="form-check-label">enable_qty</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="search_product_settings[enable_product_cost]"
                    value="1" {{ !empty($search_product_settings['enable_product_cost']) ? 'checked' : '' }}>
                <label class="form-check-label">enable_product_cost</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                    name="search_product_settings[enable_product_supplier]" value="1"
                    {{ !empty($search_product_settings['enable_product_supplier']) ? 'checked' : '' }}>
                <label class="form-check-label">enable_product_supplier</label>
            </div>
        </div>
    </div>
</div>
