<div class="row">
    <h4 class="card-title text-primary">Stores Settings</h4>
    <p class="text-muted">Configure stores default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">default_store</label>
            <select name="default_store" class="form-control select">
                <option value="">Select store</option>
                <!-- You might need to pass $stores from controller -->
                @if (isset($stores))
                    @foreach ($stores as $key => $val)
                        <option value="{{ $key }}" {{ $business->default_store == $key ? 'selected' : '' }}>
                            {{ $val }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
</div>
