@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-globe"></i> Localization</h1>
        <p class="settings-section-subtitle">Language, date formats, currency, and regional preferences.</p>
    </div>
</div>
<form id="settingsForm" data-section="localization">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-earth-africa"></i> Regional Settings</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('regional', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-hashtag"></i> Number Formatting</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('numbers', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Localization</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
