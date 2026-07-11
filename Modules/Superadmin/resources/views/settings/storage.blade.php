@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-hard-drive"></i> File Storage</h1>
        <p class="settings-section-subtitle">Configure where uploaded files are stored — Local, S3, or Cloudinary.</p>
    </div>
</div>
<form id="settingsForm" data-section="storage">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-gear"></i> General Storage</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('general', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-card" data-depends="storage.driver:s3">
        <div class="settings-card-header"><i class="fa-brands fa-aws"></i> Amazon S3 Configuration</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('s3', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Storage Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
