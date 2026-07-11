@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-lock"></i> Security</h1>
        <p class="settings-section-subtitle">IP access control, rate limiting, HTTPS, audit logging, and maintenance mode.</p>
    </div>
</div>
<form id="settingsForm" data-section="security">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-network-wired"></i> Access Control</div>
        <div class="settings-card-body">
            @foreach($settings->get('access', collect()) as $setting)
                @include('superadmin::settings.partials._field', ['setting' => $setting])
            @endforeach
        </div>
    </div>
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-gauge-high"></i> Rate Limiting</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('rate', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-shield"></i> Security Headers & Audit</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('headers', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
                @foreach($settings->get('audit', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-card border-danger">
        <div class="settings-card-header text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Maintenance Mode</div>
        <div class="settings-card-body">
            @foreach($settings->get('maintenance', collect()) as $setting)
                @include('superadmin::settings.partials._field', ['setting' => $setting])
            @endforeach
        </div>
    </div>
    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Security Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
