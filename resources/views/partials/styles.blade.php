<!-- Favicon -->
@php $faviconUrl = brand('favicon'); @endphp
@if($faviconUrl)
<link rel="shortcut icon" type="image/x-icon" href="{{ $faviconUrl }}">
@endif

<!-- DataTables CSS -->
<!-- CSS moved to resources/css/app.scss bundle -->

<link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('js/plugins/bootstrap-fileinput/fileinput.min.css') }}">
@vite([
    'resources/assets/css/bootstrap.min.css',
    'resources/assets/css/line-awesome.min.css',
    'resources/assets/css/material.css',
    'resources/assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css',
    'resources/assets/css/style.css',
    'resources/css/app.scss',
])
<!-- Vendor CSS -->
@stack('vendor-styles')
@yield('vendor-styles')
<!-- Custom CSS -->
@livewireStyles
@stack('page-styles')
@stack('style')