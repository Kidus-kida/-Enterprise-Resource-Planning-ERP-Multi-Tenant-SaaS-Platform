@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-envelope"></i> Email Settings</h1>
        <p class="settings-section-subtitle">Configure SMTP and outgoing mail settings. Test your configuration instantly.</p>
    </div>
</div>
<form id="settingsForm" data-section="email">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-gear"></i> Mail Driver & Sender</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('general', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-card" id="smtpCard" data-depends="email.mail_driver:smtp">
        <div class="settings-card-header"><i class="fa-solid fa-server"></i> SMTP Configuration</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('smtp', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Test Email --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-paper-plane"></i> Test Email</div>
        <div class="settings-card-body">
            <p class="text-muted small mb-3">Send a test email to verify your SMTP configuration is working.</p>
            <div class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label class="setting-label">Send Test Email To</label>
                    <input type="email" class="form-control" id="testEmailAddress" placeholder="recipient@example.com">
                </div>
                <button type="button" class="btn btn-outline-primary" id="sendTestEmailBtn">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send Test
                </button>
            </div>
            <div class="test-email-result mt-2" id="testEmailResult" style="display:none;"></div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Email Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
