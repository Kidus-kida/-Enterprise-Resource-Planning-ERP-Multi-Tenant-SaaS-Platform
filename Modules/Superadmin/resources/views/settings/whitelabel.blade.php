@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-tag"></i> White-Label Branding
        </h1>
        <p class="settings-section-subtitle">Systems names, logos, email signatures, welcome messages, loading screens, and general branding configuration.</p>
    </div>
</div>

<form id="settingsForm" data-section="whitelabel">
    @csrf

    {{-- System Identity --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-fingerprint"></i> System Identity & Logos
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('identity', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Auth Pages Branding --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-right-to-bracket"></i> Login Page Branding
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('login', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Dashboard Welcome --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-table-columns"></i> Dashboard Welcome Banner
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('dashboard', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Error Customization --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-triangle-exclamation"></i> Error & Maintenance Logos
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('error', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Whitelabel Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
