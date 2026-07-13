@use('Illuminate\Support\Facades\Vite')
<!DOCTYPE html>
<html lang="{{ !empty(LocaleSettings('lang')) ? LocaleSettings('lang') : 'en' }}"
    data-layout="{{ !empty(Theme('layout')) ? Theme('layout') : 'vertical' }}"
    data-layout-mode="{{ !empty(Theme('color_scheme')) ? Theme('color_scheme') : 'orange' }}"
    data-layout-width="{{ !empty(Theme('layout_width')) ? Theme('layout_width') : 'fluid' }}"
    data-layout-position="{{ !empty(Theme('layout_position')) ? Theme('layout_position') : 'fluid' }}"
    data-topbar="{{ !empty(Theme('topbar_color')) ? Theme('topbar_color') : 'default' }}" 
    data-layout-style="{{ !empty(Theme('sidebar_view')) ? Theme('sidebar_view') : 'default' }}"
    data-sidebar="{{ !empty(Theme('sidebar_color')) ? Theme('sidebar_color') : 'dark' }}"
    data-sidebar-size="{{ !empty(Theme('sidebar_size')) ? Theme('sidebar_size'): 'lg' }}" 
    data-sidebar-image="{{ asset('assets/img/laptop.png') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-param" content="_token" />
    <title>{{ $pageTitle ?? '' }} - {{ setting('whitelabel.browser_title', !empty(Theme('name')) ? Theme('name') : config('app.name')) }}</title>
    @php $faviconUrl = brandingAsset('favicon'); @endphp
    @if($faviconUrl)
        <link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif
    @php
        $theme = setting('appearance.theme', 'default');
        $presets = [
            'default' => ['primary' => '#ff9b44', 'sidebar' => '#2c3e50'],
            'blue' => ['primary' => '#0d6efd', 'sidebar' => '#1a2035'],
            'dark' => ['primary' => '#6c757d', 'sidebar' => '#121212'],
            'corporate' => ['primary' => '#0f4c81', 'sidebar' => '#1a3352'],
            'green' => ['primary' => '#198754', 'sidebar' => '#1a3d2b'],
        ];

        if ($theme === 'custom') {
            $primaryColor = setting('appearance.primary_color', '#ff9b44');
            $sidebarColor = setting('appearance.sidebar_color', '#2c3e50');
            $secondaryColor = setting('appearance.secondary_color', '#858796');
            $accentColor = setting('appearance.accent_color', '#f6c23e');
            $successColor = setting('appearance.success_color', '#1cc88a');
            $dangerColor = setting('appearance.danger_color', '#e74a3b');
            $warningColor = setting('appearance.warning_color', '#f6c23e');
            $infoColor = setting('appearance.info_color', '#36b9cc');
            $sidebarTextColor = setting('appearance.sidebar_text_color', '#ffffff');
            $navbarColor = setting('appearance.navbar_color', '#ffffff');
            $headerColor = setting('appearance.header_color', '#f8f9fa');
        } else {
            $primaryColor = $presets[$theme]['primary'] ?? '#ff9b44';
            $sidebarColor = $presets[$theme]['sidebar'] ?? '#2c3e50';
            $secondaryColor = '#858796';
            $accentColor = '#f6c23e';
            $successColor = '#1cc88a';
            $dangerColor = '#e74a3b';
            $warningColor = '#f6c23e';
            $infoColor = '#36b9cc';
            $sidebarTextColor = '#ffffff';
            $navbarColor = '#ffffff';
            $headerColor = '#f8f9fa';
        }
    @endphp
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --primary-hover-color: color-mix(in srgb, var(--primary-color) 85%, black);
            --font-color: {{ Theme('font_color') ?? '#1f1f1f' }};
            
            --secondary-color: {{ $secondaryColor }};
            --accent-color: {{ $accentColor }};
            --success-color: {{ $successColor }};
            --danger-color: {{ $dangerColor }};
            --warning-color: {{ $warningColor }};
            --info-color: {{ $infoColor }};
            
            --sidebar-bg: {{ $sidebarColor }};
            --sidebar-text: {{ $sidebarTextColor }};
            --navbar-bg: {{ $navbarColor }};
            --header-bg: {{ $headerColor }};
            
            --border-radius: {{ setting('appearance.border_radius', '4') }}px;
            --font-family: {{ setting('appearance.font_family', 'Inter, sans-serif') }};
            --font-size: {{ setting('appearance.font_size', '14px') }};
            
            --card-shadow: {{ setting('appearance.shadows', '1') ? '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)' : 'none' }};
            --animation-speed: {{ setting('appearance.animations', '1') ? '0.2s' : '0s' }};
        }
    </style>
    @include('partials.styles')
</head>

<body @isset($bodyClass) class="{{ $bodyClass }} mini-sidebar" @else class="mini-sidebar" @endisset>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        @yield('content')
    </div>
    <!-- /Main Wrapper -->
    @include('partials.scripts')
    @stack('page-scripts')
    @yield('modals')
</body>

</html>
