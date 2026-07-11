@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-shield-halved"></i> Authentication
        </h1>
        <p class="settings-section-subtitle">Login behavior, password policy, session management, and 2FA.</p>
    </div>
</div>

<form id="settingsForm" data-section="authentication">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-right-to-bracket"></i> Login Options</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('login', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-hourglass-half"></i> Session</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('session', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-mobile-screen"></i> Two-Factor Authentication</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('2fa', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-key"></i> Password Policy</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('password', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-ban"></i> Lockout Settings</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('lockout', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Authentication Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
