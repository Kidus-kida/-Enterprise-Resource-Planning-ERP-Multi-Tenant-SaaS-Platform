<div class="row">
    <h4 class="card-title text-primary">Sales Settings</h4>
    <p class="text-muted">Configure sales default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">default_sales_discount <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-percent"></i></span>
                <input type="text" name="default_sales_discount" class="form-control"
                    value="{{ number_format(optional($business)->default_sales_discount, 2) }}">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">default_sales_tax</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <select name="default_sales_tax" class="form-control select">
                    <option value="">default_sales_tax</option>
                    @foreach ($tax_rates as $key => $val)
                        <option value="{{ $key }}"
                            {{ optional($business)->default_sales_tax == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">sales_commission_agent</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <select name="sales_cmsn_agnt" class="form-control select">
                    @foreach ($commission_agent_dropdown as $key => $val)
                        <option value="{{ $key }}" {{ optional($business)->sales_cmsn_agnt == $key ? 'selected' : '' }}>
                            {{ $val }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">sales_item_addition_method</label>
            <select name="item_addition_method" class="form-control select">
                <option value="0" {{ optional($business)->item_addition_method == 0 ? 'selected' : '' }}>add_item_in_new_row
                </option>
                <option value="1" {{ optional($business)->item_addition_method == 1 ? 'selected' : '' }}>increase_item_qty
                </option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">service_item_addition_method</label>
            <select name="service_addition_method" class="form-control select">
                <option value="0" {{ optional($business)->service_addition_method == 0 ? 'selected' : '' }}>add_service_in_new_row
                </option>
                <option value="1" {{ optional($business)->service_addition_method == 1 ? 'selected' : '' }}>
                    increase_item_qty</option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_msp">sale_price_is_minimum_sale_price</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[enable_msp]" id="enable_msp"
                    value="1" {{ !empty($pos_settings['enable_msp']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="price_later_sales">price_later</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[price_later_sales]"
                    id="price_later_sales" value="1"
                    {{ !empty($pos_settings['price_later_sales']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="allow_overselling">allow_overselling</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[allow_overselling]"
                    id="allow_overselling" value="1"
                    {{ !empty($pos_settings['allow_overselling']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="sold_product_list">sold_product_list</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[sold_product_list]"
                    id="sold_product_list" value="1"
                    {{ !empty($pos_settings['sold_product_list']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_line_discount">enable_line_discount</label>
                <input class="form-check-input ms-0" type="checkbox" name="pos_settings[enable_line_discount]"
                    id="pos_enable_line_discount" value="1"
                    {{ !empty($pos_settings['enable_line_discount']) ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
