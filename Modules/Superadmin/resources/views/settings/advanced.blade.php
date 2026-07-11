@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-terminal"></i> Advanced (White-Labeling)</h1>
        <p class="settings-section-subtitle">Developer systems configuration, white-label branding, PDF/Invoice formats, and custom titles.</p>
    </div>
</div>

<form id="settingsForm" data-section="whitelabel">
    @csrf

    {{-- White Label Branding --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-copyright"></i> White-Label Branding</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('branding', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- PDF & Printing defaults --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-file-pdf"></i> PDF & Invoice Layouts</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('print', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Advanced Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
