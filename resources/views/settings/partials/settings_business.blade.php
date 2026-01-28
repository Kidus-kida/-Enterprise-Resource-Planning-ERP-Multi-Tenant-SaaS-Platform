<div class="row">
    <h4 class="card-title text-primary">Business Settings</h4>
    <p class="text-muted">Configure business default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Business Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ optional($business)->name ?? '' }}"
                required readonly>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $business->start_date ?? '' }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Default Profit Percent <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-plus-circle"></i></span>
                <input type="text" name="default_profit_percent" class="form-control"
                    value="{{ number_format($business->default_profit_percent ?? 0, 2) }}">
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Currency <span class="text-danger">*</span></label>
            <select name="currency_id" class="form-control select" required>
                <option value="">Select Currency</option>
                @foreach ($currencies as $id => $currency)
                    <option value="{{ $id }}" {{ ($business->currency_id ?? '') == $id ? 'selected' : '' }}>
                        {{ $currency }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Currency Symbol Placement</label>
            <select name="currency_symbol_placement" class="form-control select">
                <option value="before" {{ ($business->currency_symbol_placement ?? '') == 'before' ? 'selected' : '' }}>
                    Before Amount</option>
                <option value="after" {{ ($business->currency_symbol_placement ?? '') == 'after' ? 'selected' : '' }}>
                    After Amount</option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Time Zone</label>
            <select name="time_zone" class="form-control select">
                <option value="">Select Timezone</option>
                @foreach ($timezone_list as $tz)
                    <option value="{{ $tz }}" {{ ($business->time_zone ?? '') == $tz ? 'selected' : '' }}>
                        {{ $tz }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Upload Logo</label>
            <input type="file" name="business_logo" class="form-control" accept="image/*">
            <small class="text-muted">Previous logo (if exists) will be replaced</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Financial Year Start Month</label>
            <select name="fy_start_month" class="form-control select">
                @foreach ($months as $key => $month)
                    <option value="{{ $key }}"
                        {{ ($business->fy_start_month ?? '') == $key ? 'selected' : '' }}>
                        {{ $month }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Stock Accounting Method <span class="text-danger">*</span></label>
            <select name="accounting_method" class="form-control select" required>
                @foreach ($accounting_methods as $key => $method)
                    <option value="{{ $key }}"
                        {{ ($business->accounting_method ?? '') == $key ? 'selected' : '' }}>
                        {{ $method }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Transaction Edit Days <span class="text-danger">*</span></label>
            <input type="number" name="transaction_edit_days" class="form-control"
                value="{{ $business->transaction_edit_days ?? '' }}" required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Date Format <span class="text-danger">*</span></label>
            <select name="date_format" class="form-control select" required>
                <option value="d-m-Y" {{ ($business->date_format ?? '') == 'd-m-Y' ? 'selected' : '' }}>
                    dd-mm-yyyy</option>
                <option value="m-d-Y" {{ ($business->date_format ?? '') == 'm-d-Y' ? 'selected' : '' }}>
                    mm-dd-yyyy</option>
                <option value="d/m/Y" {{ ($business->date_format ?? '') == 'd/m/Y' ? 'selected' : '' }}>
                    dd/mm/yyyy</option>
                <option value="m/d/Y" {{ ($business->date_format ?? '') == 'm/d/Y' ? 'selected' : '' }}>
                    mm/dd/yyyy</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Time Format <span class="text-danger">*</span></label>
            <select name="time_format" class="form-control select" required>
                <option value="12" {{ ($business->time_format ?? '') == 12 ? 'selected' : '' }}>12 Hour
                </option>
                <option value="24" {{ ($business->time_format ?? '') == 24 ? 'selected' : '' }}>24 Hour
                </option>
            </select>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Currency Precision <span class="text-danger">*</span></label>
            <input type="number" name="currency_precision" class="form-control"
                value="{{ $business->currency_precision ?? 2 }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Quantity Precision <span class="text-danger">*</span></label>
            <input type="number" name="quantity_precision" class="form-control"
                value="{{ $business->quantity_precision ?? 2 }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Font Style</label>
            <input type="text" name="font_style" class="form-control" value="{{ $business->font_style ?? '' }}"
                placeholder="Enter font style if any">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Font Size</label>
            <input type="text" name="font_size" class="form-control" value="{{ $business->font_size ?? '' }}"
                placeholder="Enter font size (e.g., 14px)">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">Registration Number</label>
            <input type="text" name="reg_no" class="form-control" value="{{ $business->reg_no ?? '' }}"
                placeholder="Business Registration Number">
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="popup_load_save_data">Enable Popup
                    Load/Save Data</label>
                <input class="form-check-input ms-0" type="checkbox" name="popup_load_save_data"
                    id="popup_load_save_data" {{ ($business->popup_load_save_data ?? 0) == 1 ? 'checked' : '' }}>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="day_end_enable">Enable Day
                    End</label>
                <input class="form-check-input ms-0" type="checkbox" name="day_end_enable" id="day_end_enable"
                    {{ ($business->day_end_enable ?? 0) == 1 ? 'checked' : '' }}>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_line_discount">Enable Line
                    Discount</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_line_discount"
                    id="enable_line_discount" {{ ($business->enable_line_discount ?? 0) == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="duplicate_orders_allowed">Allow
                    Duplicate Orders</label>
                <input class="form-check-input ms-0" type="checkbox" name="duplicate_orders_allowed"
                    id="duplicate_orders_allowed"
                    {{ ($business->duplicate_orders_allowed ?? 0) == 1 ? 'checked' : '' }}>
            </div>
        </div>
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="show_for_customers">Show for
                    Customers</label>
                <input class="form-check-input ms-0" type="checkbox" name="show_for_customers"
                    id="show_for_customers" {{ ($business->show_for_customers ?? 0) == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 {{ ($business->show_for_customers ?? 0) == 0 ? 'd-none' : '' }}" id="business_categories_div">
    <div class="col-12">
        <div class="form-group mb-3">
            <label class="form-label">Business Categories</label>
            <input type="text" name="business_categories" class="form-control"
                value="{{ $business->business_categories ?? '' }}" placeholder="Enter categories separated by comma">
        </div>
    </div>
</div>
