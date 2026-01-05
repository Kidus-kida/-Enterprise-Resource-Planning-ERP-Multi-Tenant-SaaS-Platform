<div class="row">
    <h4 class="card-title text-primary">Purchase Settings</h4>
    <p class="text-muted">Configure purchase default settings.</p>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_editing_product_from_purchase">enable_editing_product_from_purchase</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_editing_product_from_purchase"
                    id="enable_editing_product_from_purchase" value="1"
                    {{optional($business)->enable_editing_product_from_purchase == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_purchase_status">enable_purchase_status</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_purchase_status"
                    id="enable_purchase_status" value="1"
                    {{ optional($business)->enable_purchase_status == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_lot_number">enable_lot_number</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_lot_number" id="enable_lot_number"
                    value="1" {{ optional($business)->enable_lot_number == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_free_qty">enable_free_qty</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_free_qty" id="enable_free_qty"
                    value="1" {{ optional($business)->enable_free_qty == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
