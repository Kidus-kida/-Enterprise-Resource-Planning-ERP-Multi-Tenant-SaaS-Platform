<div class="row">
    <h4 class="card-title text-primary">Upload Images Settings</h4>
    <p class="text-muted">Configure upload images default settings.</p>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">upload_logo</label>
            <input type="file" name="business_logo" class="form-control" accept="image/*">
            <small class="text-muted">previous_logo_will_be_replaced</small>
            @if (!empty($business->logo))
                <div class="mt-2">
                    <img src="{{ asset('uploads/business_logos/' . $business->logo) }}" alt="Business Logo"
                        style="max-width: 150px;">
                </div>
            @endif
        </div>
    </div>
</div>
