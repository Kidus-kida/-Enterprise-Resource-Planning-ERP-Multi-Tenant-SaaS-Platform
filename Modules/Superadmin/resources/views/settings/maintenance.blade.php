@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-wrench"></i> Maintenance</h1>
        <p class="settings-section-subtitle">System maintenance operations, artisan commands, queue management, and system state.</p>
    </div>
</div>

<form id="settingsForm" data-section="maintenance">
    @csrf

    {{-- System Status --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-chart-bar"></i> System Status</div>
        <div class="settings-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <small class="text-muted d-block text-uppercase mb-1">Queue Driver</small>
                        <strong class="fs-5">{{ setting('maintenance.queue_driver', 'sync') }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <small class="text-muted d-block text-uppercase mb-1">Laravel Version</small>
                        <strong class="fs-5">{{ app()->version() }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center bg-light">
                        <small class="text-muted d-block text-uppercase mb-1">PHP Version</small>
                        <strong class="fs-5">{{ PHP_VERSION }}</strong>
                    </div>
                </div>
            </div>

            <div class="settings-grid-2 mt-4">
                @foreach($settings->get('queue', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick Artisan Tools --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-terminal"></i> Quick Commands</div>
        <div class="settings-card-body">
            <p class="text-muted small">Execute common Laravel optimization and maintenance operations directly.</p>
            <div class="d-flex flex-wrap gap-2" id="artisanConsole">
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="cache:clear">
                    <i class="fa-solid fa-broom me-1"></i> Clear Cache
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="config:clear">
                    <i class="fa-solid fa-file-code me-1"></i> Clear Config
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="route:clear">
                    <i class="fa-solid fa-road me-1"></i> Clear Routes
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="view:clear">
                    <i class="fa-solid fa-eye-slash me-1"></i> Clear Views
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="optimize">
                    <i class="fa-solid fa-bolt me-1"></i> Optimize App
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary artisan-btn" data-command="queue:restart">
                    <i class="fa-solid fa-rotate me-1"></i> Restart Queues
                </button>
            </div>
            <div class="artisan-output-wrap mt-3" style="display:none;">
                <label class="setting-label">Command Output</label>
                <pre class="bg-dark text-white p-3 rounded small mb-0" id="artisanOutputPanel"></pre>
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Maintenance Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
