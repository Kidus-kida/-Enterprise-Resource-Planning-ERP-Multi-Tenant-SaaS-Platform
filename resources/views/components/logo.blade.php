<!-- Logo -->
<div class="header-left">
    <a href="{{ route('dashboard') }}" class="logo">
        @if(setting('whitelabel.logo'))
            <img src="{{ Storage::url(setting('whitelabel.logo')) }}" width="40" height="40" alt="Logo">
        @else
            <img src="{{ asset('images/logo.png') }}" width="40" height="40" alt="Logo">
        @endif
    </a>
    <a href="{{ route('dashboard') }}" class="logo2">
        @if(setting('whitelabel.logo_dark'))
            <img src="{{ Storage::url(setting('whitelabel.logo_dark')) }}" width="40" height="40" alt="Logo">
        @else
            <img src="{{ asset('images/logo2.png') }}" width="40" height="40" alt="Logo">
        @endif
    </a>
</div>
<!-- /Logo -->
