@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-plug"></i> Integrations</h1>
        <p class="settings-section-subtitle">Configure API keys and credentials for third-party services. Sensitive values are encrypted.</p>
    </div>
</div>
<form id="settingsForm" data-section="integration">
    @csrf

    @foreach(['google'=>['fa-brands fa-google','Google OAuth & Maps'],'microsoft'=>['fa-brands fa-microsoft','Microsoft / Azure'],'stripe'=>['fa-brands fa-stripe-s','Stripe Payments'],'paypal'=>['fa-brands fa-paypal','PayPal Payments'],'telebirr'=>['fa-solid fa-mobile-screen','Telebirr (Ethiopia)'],'pusher'=>['fa-solid fa-bolt','Pusher (Real-Time)'],'zoom'=>['fa-brands fa-zoom','Zoom Meetings']] as $ch=>[$icon,$label])
    <div class="settings-card">
        <div class="settings-card-header"><i class="{{ $icon }}"></i> {{ $label }}</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get($ch, collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
            @if($settings->get($ch, collect())->isEmpty())
            <p class="text-muted small mb-0">No settings configured for this integration.</p>
            @endif
        </div>
    </div>
    @endforeach

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Integration Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
