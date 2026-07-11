@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-gear"></i> General Settings
        </h1>
        <p class="settings-section-subtitle">Core ERP system configuration — name, timezone, formats, and defaults.</p>
    </div>
</div>

<form id="settingsForm" data-section="general">
    @csrf

    {{-- System Identity --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-id-card"></i> System Identity
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('system', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Support --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-headset"></i> Support Contact
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('support', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Defaults --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-sliders"></i> Regional & Format Defaults
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('defaults', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save General Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
