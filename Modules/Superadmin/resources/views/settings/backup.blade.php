@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-database"></i> Backup & Restore</h1>
        <p class="settings-section-subtitle">Schedule automatic backups, run manual backups, and restore from a previous backup.</p>
    </div>
</div>
<form id="settingsForm" data-section="backup">
    @csrf
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-clock-rotate-left"></i> Backup Schedule</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('schedule', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>
    {{-- Manual Backup Actions --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-play"></i> Manual Actions</div>
        <div class="settings-card-body">
            <div class="d-flex flex-wrap gap-3">
                <button type="button" class="btn btn-outline-primary artisan-btn" data-command="">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Create Backup Now
                </button>
                <a href="#" class="btn btn-outline-success">
                    <i class="fa-solid fa-download me-1"></i> Download Latest Backup
                </a>
            </div>
        </div>
    </div>
    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Backup Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
