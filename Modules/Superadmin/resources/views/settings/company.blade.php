@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-building"></i> Company Settings
        </h1>
        <p class="settings-section-subtitle">Organization identity, contact details, and legal information.</p>
    </div>
</div>

<form id="settingsForm" data-section="company">
    @csrf

    {{-- Identity & Branding --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-fingerprint"></i> Identity & Branding
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('identity', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-address-card"></i> Contact Information
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('contact', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-location-dot"></i> Address
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('address', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Legal --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-scale-balanced"></i> Legal & Registration
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('legal', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Company Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
