<div class="row">
    <h4 class="card-title text-primary">Upload Images Settings</h4>
    <p class="text-muted">Configure upload images default settings.</p>
    <div class="col-md-6">
            <div class="form-group mb-3">
                <label class="form-label">Default Logo (shown if business logo not set)</label>
                <input type="file" name="business_logo_default" class="form-control" accept="image/*">
                <small class="text-muted">Will be used as a fallback logo.</small>
                @if(!empty($business->business_logo_default))
                    <div class="mt-2">
                        <p class="text-success small mb-1">Current Default Logo:</p>
                        <img src="{{ asset('uploads/business_logos/' . $business->business_logo_default) }}" alt="Default Logo" style="max-width: 150px; border: 1px solid #ddd; padding: 5px;">
                    </div>
                @endif
            </div>
    </div>
</div>
