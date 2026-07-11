@extends('superadmin::settings.layout')
@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title"><i class="fa-solid fa-cubes"></i> Module Management</h1>
        <p class="settings-section-subtitle">Enable or disable ERP modules. Disabled modules are automatically hidden from navigation.</p>
    </div>
</div>
<form id="settingsForm" data-section="modules">
    @csrf
    @php
        $modules = ['hr','payroll','crm','inventory','accounting','attendance','recruitment','performance','training','assets','projects','helpdesk'];
        $moduleIcons = ['hr'=>'fa-users','payroll'=>'fa-money-bill-wave','crm'=>'fa-handshake','inventory'=>'fa-boxes-stacked','accounting'=>'fa-calculator','attendance'=>'fa-clock','recruitment'=>'fa-person-walking','performance'=>'fa-chart-line','training'=>'fa-graduation-cap','assets'=>'fa-laptop','projects'=>'fa-diagram-project','helpdesk'=>'fa-headset'];
    @endphp

    <div class="modules-grid">
        @foreach($modules as $slug)
        @php
            $moduleSettings = $settings->get($slug, collect());
            $enabledSetting = $moduleSettings->where('key', "module.{$slug}.enabled")->first();
            $labelSetting   = $moduleSettings->where('key', "module.{$slug}.menu_label")->first();
            $isEnabled      = ($allSettings["module.{$slug}.enabled"] ?? '1') == '1';
        @endphp
        <div class="module-toggle-card {{ $isEnabled ? 'enabled' : 'disabled' }}">
            <div class="module-card-icon">
                <i class="fa-solid {{ $moduleIcons[$slug] ?? 'fa-cube' }}"></i>
            </div>
            <div class="module-card-info">
                <span class="module-label">{{ $allSettings["module.{$slug}.menu_label"] ?? ucfirst($slug) }}</span>
                <span class="module-key">module.{{ $slug }}</span>
            </div>
            <div class="module-card-toggle">
                @if($enabledSetting)
                    @include('superadmin::settings.partials._field', ['setting' => $enabledSetting])
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Module Settings</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
