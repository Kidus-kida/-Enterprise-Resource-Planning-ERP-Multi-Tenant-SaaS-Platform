@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-bell"></i> Notifications</h1>
        <p class="settings-section-subtitle">Enable or disable notification channels: Email, SMS, Push, Slack, Telegram, WhatsApp.</p>
    </div>
</div>
<form id="settingsForm" data-section="notification">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-toggle-on"></i> Notification Channels</div>
        <div class="settings-card-body">
            <div class="notification-channels-grid">
                @foreach($settings->get('channels', collect()) as $setting)
                <div class="notification-channel-card">
                    <div class="nc-icon">
                        @php
                            $icons = ['email_enabled'=>'fa-envelope','sms_enabled'=>'fa-comment-sms','push_enabled'=>'fa-bell','slack_enabled'=>'fa-brands fa-slack','telegram_enabled'=>'fa-brands fa-telegram','whatsapp_enabled'=>'fa-brands fa-whatsapp'];
                            $slug = explode('.',$setting->key)[1] ?? '';
                            $icon = $icons[$slug] ?? 'fa-bell';
                        @endphp
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <div class="nc-info">
                        <span class="nc-label">{{ $setting->label }}</span>
                    </div>
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- Webhook credentials --}}
    @foreach(['slack','telegram'] as $ch)
    <div class="settings-card" data-depends="notification.{{ $ch }}_enabled:1">
        <div class="settings-card-header"><i class="fa-brands fa-{{ $ch }}"></i> {{ ucfirst($ch) }} Configuration</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get($ch, collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Notification Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
