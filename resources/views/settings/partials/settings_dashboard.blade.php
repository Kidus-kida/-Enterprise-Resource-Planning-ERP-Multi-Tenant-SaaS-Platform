<div class="row">
    <h4 class="card-title text-primary">Dashboard Settings</h4>
    <p class="text-muted">Configure dashboard default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">view_stock_expiry_alert_for <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                <input type="number" name="stock_expiry_alert_days" class="form-control"
                    value="{{ optional($business)->stock_expiry_alert_days }}">
                <span class="input-group-text">days</span>
            </div>
        </div>
    </div>
</div>
