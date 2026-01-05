<div class="row">
     <h4 class="card-title text-primary">Tax Settings</h4>
        <p class="text-muted">Configure tax default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">tax_1_name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <input type="text" name="tax_label_1" class="form-control" value="{{ $business->tax_label_1 ?? '' }}"
                    placeholder="tax_1_name">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">tax_1_no</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <input type="text" name="tax_number_1" class="form-control" value="{{ $business->tax_number_1 ?? '' }}">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">tax_2_name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <input type="text" name="tax_label_2" class="form-control" value="{{ $business->tax_label_2 ?? '' }}"
                    placeholder="tax_1_placeholder">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">tax_2_no</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-info"></i></span>
                <input type="text" name="tax_number_2" class="form-control" value="{{ $business->tax_number_2 ?? ''}}">
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_inline_tax">enable_inline_tax</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_inline_tax" id="enable_inline_tax"
                 {{ optional($business)->enable_inline_tax == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
