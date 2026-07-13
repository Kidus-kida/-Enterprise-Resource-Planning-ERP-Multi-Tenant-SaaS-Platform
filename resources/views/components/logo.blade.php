@php
    $brandName = appBrandName();
    $brandShortName = setting('whitelabel.short_name', $brandName);
    $logoUrl = brand('logo');
    $darkLogoUrl = brand('dark_logo');
    $logoAlt = $brandName;
@endphp
<!-- Logo -->
<div class="header-left">
    <a href="{{ route('dashboard') }}" class="logo" style="font-size: 20px; font-weight: bold; color: #333; text-decoration: none; display: flex; align-items: center; padding: 10px;">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" style="max-height: 32px; max-width: 140px; object-fit: contain;">
        @else
            <span>{{ $brandShortName }}</span>
        @endif
    </a>
    <a href="{{ route('dashboard') }}" class="logo2" style="font-size: 20px; font-weight: bold; color: #fff; text-decoration: none; display: flex; align-items: center; padding: 10px;">
        @if($darkLogoUrl)
            <img src="{{ $darkLogoUrl }}" alt="{{ $logoAlt }}" style="max-height: 32px; max-width: 140px; object-fit: contain;">
        @else
            <span>{{ $brandShortName }}</span>
        @endif
    </a>
</div>
<!-- /Logo -->
