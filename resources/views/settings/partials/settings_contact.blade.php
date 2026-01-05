<div class="row">
    <div class="col-md-12">
        <h4 class="card-title text-primary">Contact Settings</h4>
        <p class="text-muted">Configure contact default settings.</p>
        <div class="form-group mb-3">
            <label class="form-label">Default Credit Limit</label>
            <input type="number" name="default_credit_limit" class="form-control"
                value="{{ $business->default_credit_limit ?? 0 }}">
        </div>
    </div>
</div>
