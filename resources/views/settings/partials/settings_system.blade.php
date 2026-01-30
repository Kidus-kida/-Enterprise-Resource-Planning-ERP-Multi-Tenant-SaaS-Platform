<div class="row">
    <h4 class="card-title text-primary">System Settings</h4>
    <p class="text-muted">Configure system default settings.</p>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">theme_color</label>
            <select name="theme_color" class="form-control select">
                <option value="">please_select</option>
                @foreach ($theme_colors as $key => $val)
                    <option value="{{ $key }}" {{ optional($business)->theme_color == $key ? 'selected' : '' }}>
                        {{ $val }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <label class="form-label">default_datatable_page_entries</label>
            <select name="common_settings[default_datatable_page_entries]" class="form-control select">
                @foreach ([25, 50, 100, 200, 500, 1000] as $val)
                    <option value="{{ $val }}"
                        {{ ($common_settings['default_datatable_page_entries'] ?? 25) == $val ? 'selected' : '' }}>
                        {{ $val }}</option>
                @endforeach
                <option value="-1"
                    {{ ($common_settings['default_datatable_page_entries'] ?? '') == -1 ? 'selected' : '' }}>
                    all</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-3">
            <div class="form-check form-switch px-0">
                <label class="form-check-label ms-5" for="enable_tooltip">show_help_text</label>
                <input class="form-check-input ms-0" type="checkbox" name="enable_tooltip" id="enable_tooltip"
                    value="1" {{optional($business)->enable_tooltip == 1 ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>

{{-- Cache/Permission Management (for Tenant Admins only) --}}
@if(auth()->user() && auth()->user()->type !== \App\Enums\UserType::SUPERADMIN)
<div class="row mt-4">
    <div class="col-12">
        <hr>
        <h5 class="text-muted"><i class="fa fa-wrench"></i> Maintenance</h5>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6>Clear System Cache</h6>
                <p class="text-muted small">Use this if menus or permissions are not displaying correctly after system updates.</p>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearPermissionCache()">
                    <i class="fa fa-refresh"></i> Clear Cache
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function clearPermissionCache() {
    if (!confirm('This will clear the system cache. Continue?')) return;
    
    fetch('/clear-permission-cache')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully! The page will now reload.');
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            alert('Error clearing cache: ' + err.message);
        });
}
</script>
@endif
