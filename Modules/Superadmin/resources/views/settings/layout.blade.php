@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/superadmin/css/settings.css') }}">
@endpush

@section('page-content')
<div class="settings-wrapper">

    {{-- ===== Sidebar ===== --}}
    <aside class="settings-sidebar" id="settingsSidebar">
        <div class="settings-sidebar-header">
            <i class="fa-solid fa-sliders"></i>
            <span>System Settings</span>
        </div>

        {{-- Global Search --}}
        <div class="settings-search-wrap">
            <button class="settings-search-btn" id="openSettingsSearch" title="Search settings (Ctrl+K)">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Search settings...</span>
                <kbd>Ctrl K</kbd>
            </button>
        </div>

        <nav class="settings-nav">
            @php
                use Modules\Superadmin\Settings\SettingCategory;
                $navItems = [
                    [SettingCategory::GENERAL,        'General Settings',   'fa-solid fa-gear'],
                    [SettingCategory::COMPANY,         'Company',            'fa-solid fa-building'],
                    [SettingCategory::APPEARANCE,      'Appearance',         'fa-solid fa-palette'],
                    [SettingCategory::AUTHENTICATION,  'Authentication',     'fa-solid fa-shield-halved'],
                    [SettingCategory::LOCALIZATION,    'Localization',       'fa-solid fa-globe'],
                    [SettingCategory::EMAIL,           'Email',              'fa-solid fa-envelope'],
                    [SettingCategory::NOTIFICATION,    'Notifications',      'fa-solid fa-bell'],
                    [SettingCategory::STORAGE,         'File Storage',       'fa-solid fa-hard-drive'],
                    [SettingCategory::BACKUP,          'Backup & Restore',   'fa-solid fa-database'],
                    [SettingCategory::SECURITY,        'Security',           'fa-solid fa-lock'],
                    [SettingCategory::MODULES,         'Modules',            'fa-solid fa-cubes'],
                    [SettingCategory::INTEGRATION,     'Integrations',       'fa-solid fa-plug'],
                    [SettingCategory::MAINTENANCE,     'Maintenance',        'fa-solid fa-wrench'],
                    [SettingCategory::LOGS,            'Logs',               'fa-solid fa-file-lines'],
                    [SettingCategory::LICENSE,         'License',            'fa-solid fa-key'],
                    [SettingCategory::ADVANCED,        'Advanced',           'fa-solid fa-terminal'],
                    [SettingCategory::WHITELABEL,      'White-Labeling',     'fa-solid fa-tag'],
                    [SettingCategory::MENU,            'Menu Builder',       'fa-solid fa-bars'],
                    [SettingCategory::DASHBOARD,       'Dashboard Builder',  'fa-solid fa-table-columns'],
                ];
            @endphp

            @foreach($navItems as [$cat, $label, $icon])
            <a href="{{ route('superadmin.settings.show', $cat) }}"
               class="settings-nav-item {{ $category === $cat ? 'active' : '' }}">
                <i class="{{ $icon }}"></i>
                <span>{{ $label }}</span>
                @if($category === $cat)
                    <i class="fa-solid fa-chevron-right ms-auto"></i>
                @endif
            </a>
            @endforeach
        </nav>

        {{-- Import / Export --}}
        <div class="settings-sidebar-footer">
            <a href="{{ route('superadmin.settings.export') }}" class="btn-sidebar-action" title="Export all settings as JSON">
                <i class="fa-solid fa-file-export"></i> Export
            </a>
            <button class="btn-sidebar-action" data-bs-toggle="modal" data-bs-target="#importModal" title="Import settings from JSON">
                <i class="fa-solid fa-file-import"></i> Import
            </button>
        </div>
    </aside>

    {{-- ===== Main Content ===== --}}
    <div class="settings-main">

        {{-- Top Bar --}}
        <div class="settings-topbar">
            <button class="sidebar-toggle-btn d-lg-none" id="toggleSettingsSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="settings-breadcrumb">
                <a href="{{ route('superadmin.dashboard') }}">Super Admin</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="{{ route('superadmin.settings.index') }}">Settings</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>{{ SettingCategory::LABELS[$category] ?? ucfirst($category) }}</span>
            </div>
            <div class="settings-topbar-actions">
                <form method="POST" action="{{ route('superadmin.settings.restore-defaults', $category) }}" id="restoreDefaultsForm">
                    @csrf
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="confirmRestore()">
                        <i class="fa-solid fa-rotate-left"></i> Restore Defaults
                    </button>
                </form>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-0" role="alert">
            <i class="fa-solid fa-circle-xmark"></i>
            {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Section Content --}}
        <div class="settings-content">
            @yield('settings-content')
        </div>

    </div>
</div>

{{-- ===== Search Modal ===== --}}
<div class="settings-search-overlay" id="settingsSearchOverlay">
    <div class="settings-search-modal">
        <div class="settings-search-input-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="settingsSearchInput" placeholder="Search settings... (e.g. smtp, logo, timezone)" autocomplete="off">
            <kbd>Esc</kbd>
        </div>
        <div class="settings-search-results" id="settingsSearchResults">
            <div class="search-hint">Type at least 2 characters to search</div>
        </div>
    </div>
</div>

{{-- ===== Import Modal ===== --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-file-import me-2"></i>Import Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('superadmin.settings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">Upload a JSON file exported from this system. Non-sensitive settings will be imported; sensitive fields (passwords, secrets) will be skipped.</p>
                    <div class="mb-3">
                        <label class="form-label">Settings JSON File</label>
                        <input type="file" class="form-control" name="settings_file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Toast ===== --}}
<div class="settings-toast" id="settingsToast">
    <div class="settings-toast-inner">
        <i class="fa-solid fa-circle-check toast-icon"></i>
        <span class="toast-message">Settings saved.</span>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('modules/superadmin/js/settings.js') }}"></script>
<script>
    const SETTINGS_SEARCH_URL = '{{ route("superadmin.settings.search") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    function confirmRestore() {
        if (confirm('Restore all settings in this section to their default values?')) {
            document.getElementById('restoreDefaultsForm').submit();
        }
    }
</script>
@endpush
