@extends('superadmin::settings.layout')

@push('styles')
<style>
/* Live preview bar shown at top of appearance page */
.appearance-preview-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--bs-dark, #1e2a3a);
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 13px;
    flex-wrap: wrap;
}
.preview-swatch { width:22px;height:22px;border-radius:50%;border:2px solid rgba(255,255,255,.3); }
</style>
@endpush

@section('settings-content')
<div class="settings-section-header">
    <div>
        <h1 class="settings-section-title">
            <i class="fa-solid fa-palette"></i> Appearance
        </h1>
        <p class="settings-section-subtitle">Customize colors, fonts, layout, themes, and branding. Changes preview in real time.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success live-badge"><i class="fa-solid fa-circle" style="font-size:7px"></i> Live Preview</span>
    </div>
</div>

{{-- Live preview bar --}}
<div class="appearance-preview-bar" id="livePreviewBar">
    <span><i class="fa-solid fa-eye me-1"></i> Live Preview Active</span>
    <div class="preview-swatch" id="prevPrimary" style="background:{{ setting('appearance.primary_color','#4e73df') }}"></div>
    <div class="preview-swatch" id="prevSidebar" style="background:{{ setting('appearance.sidebar_color','#2c3e50') }}"></div>
    <div class="preview-swatch" id="prevAccent" style="background:{{ setting('appearance.accent_color','#f6c23e') }}"></div>
    <div class="preview-swatch" id="prevSuccess" style="background:{{ setting('appearance.success_color','#1cc88a') }}"></div>
</div>

<form id="settingsForm" data-section="appearance">
    @csrf

    {{-- Theme Selection --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-swatchbook"></i> Theme
        </div>
        <div class="settings-card-body">
            {{-- Theme picker cards --}}
            <div class="theme-picker-grid">
                @foreach([['default','Default','#4e73df','#2c3e50'],['blue','Blue','#0d6efd','#1a2035'],['dark','Dark','#6c757d','#121212'],['corporate','Corporate','#0f4c81','#1a3352'],['green','Green','#198754','#1a3d2b'],['custom','Custom','#e91e63','#222']] as [$val,$lbl,$primary,$sidebar])
                <label class="theme-card {{ setting('appearance.theme','default') === $val ? 'selected' : '' }}">
                    <input type="radio" name="appearance_theme" value="{{ $val }}"
                           {{ setting('appearance.theme','default') === $val ? 'checked' : '' }}
                           class="theme-radio">
                    <div class="theme-card-preview">
                        <div class="tcp-sidebar" style="background:{{ $sidebar }}"></div>
                        <div class="tcp-main">
                            <div class="tcp-header" style="background:#fff;border-bottom:2px solid {{ $primary }}"></div>
                            <div class="tcp-content">
                                <div class="tcp-btn" style="background:{{ $primary }}"></div>
                            </div>
                        </div>
                        @if(setting('appearance.theme','default') === $val)
                        <div class="theme-check"><i class="fa-solid fa-check"></i></div>
                        @endif
                    </div>
                    <span class="theme-card-label">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>

            {{-- Dark / RTL switches --}}
            <div class="settings-grid-2 mt-3">
                @foreach($settings->get('theme', collect()) as $setting)
                    @if($setting->key !== 'appearance.theme')
                        @include('superadmin::settings.partials._field', ['setting' => $setting])
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Colors (shown only when theme = custom) --}}
    <div class="settings-card" id="colorsCard" data-depends="appearance.theme:custom"
         style="{{ setting('appearance.theme') === 'custom' ? '' : 'display:none;' }}">
        <div class="settings-card-header">
            <i class="fa-solid fa-circle-half-stroke"></i> Brand Colors
            <small class="ms-2 text-muted">(Custom theme only)</small>
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-3">
                @foreach($settings->get('colors', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Layout --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-table-columns"></i> Layout Options
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('layout', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Typography --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-font"></i> Typography
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('typography', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Effects --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-sparkles"></i> Visual Effects
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-3">
                @foreach($settings->get('effects', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Backgrounds --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-image"></i> Backgrounds
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('backgrounds', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Login Page --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-right-to-bracket"></i> Login Page
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-2">
                @foreach($settings->get('login', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Loader --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-spinner"></i> Page Loader
        </div>
        <div class="settings-card-body">
            <div class="settings-grid-3">
                @foreach($settings->get('loader', collect()) as $setting)
                    @include('superadmin::settings.partials._field', ['setting' => $setting])
                @endforeach
            </div>
        </div>
    </div>

    {{-- Custom Code --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="fa-solid fa-code"></i> Custom Code
            <span class="badge bg-warning ms-2" style="font-size:10px;">Advanced</span>
        </div>
        <div class="settings-card-body">
            @foreach($settings->get('custom', collect()) as $setting)
                @include('superadmin::settings.partials._field', ['setting' => $setting])
            @endforeach
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary btn-save-settings">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <span class="btn-text">Save Appearance</span>
            <span class="btn-loading d-none"><i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...</span>
        </button>
    </div>
</form>
@endsection
