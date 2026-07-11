@extends('superadmin::settings.layout')

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-key"></i> System License</h1>
        <p class="settings-section-subtitle">Manage system activation, license verification, support subscription, and updates.</p>
    </div>
</div>

<form id="settingsForm" data-section="license">
    @csrf

    {{-- License Activation Status Banner --}}
    <div class="settings-card mb-4 {{ setting('license.status', 'active') === 'active' ? 'border-success' : 'border-danger' }}">
        <div class="settings-card-body d-flex align-items-center gap-3">
            <div class="fs-1 {{ setting('license.status', 'active') === 'active' ? 'text-success' : 'text-danger' }}">
                <i class="fa-solid {{ setting('license.status', 'active') === 'active' ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold">
                    {{ setting('license.status', 'active') === 'active' ? 'Product License Key Active' : 'Product Unactivated' }}
                </h4>
                <p class="text-muted small mb-0">
                    {{ setting('license.status', 'active') === 'active' 
                        ? 'Your ERP Super Admin control center is authenticated and receiving official software updates.' 
                        : 'Please enter a valid license key below to activate all modules and updates.' }}
                </p>
            </div>
            @if(setting('license.status', 'active') === 'active')
                <span class="badge bg-success ms-auto px-3 py-2 fs-7">ACTIVATED</span>
            @else
                <span class="badge bg-danger ms-auto px-3 py-2 fs-7">INACTIVE</span>
            @endif
        </div>
    </div>

    {{-- Credentials --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="fa-solid fa-file-invoice-dollar"></i> Activation Details</div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('license', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save License Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
