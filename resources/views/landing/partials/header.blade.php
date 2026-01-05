@php
    $navItems = [
        ['label' => 'Apps', 'route' => 'landing.apps'],
        ['label' => 'Pricing', 'route' => 'landing.pricing'],
        ['label' => 'Industries', 'route' => 'landing.industries'],
        ['label' => 'Services', 'route' => 'landing.services'],
        ['label' => 'Resources', 'route' => 'landing.resources'],
    ];
@endphp

<header class="sticky top-0 z-50 transition-all duration-300 backdrop-blur-md bg-white/70 border-b border-white/20">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4">
        {{-- Logo --}}
        <a href="{{ route('landing.home') }}" class="flex items-center gap-2 group">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand to-accent text-white font-bold text-lg shadow-lg group-hover:scale-105 transition-transform">
                T
            </div>
            <div class="flex flex-col leading-tight">
                <span class="text-xl font-bold tracking-tight text-slate-900">
                    Tewos<span class="text-brand">HR</span>
                </span>
            </div>
        </a>

        {{-- Desktop Nav --}}
        <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex">
            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'text-brand' : 'hover:text-brand' }} transition-colors relative group">
                    {{ $item['label'] }}
                    <span class="absolute -bottom-1 left-0 h-0.5 bg-brand transition-all {{ $isActive ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
                </a>
            @endforeach
        </nav>

        {{-- Actions --}}
        <div class="hidden items-center gap-4 md:flex">
            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-brand transition-colors">
                Sign in
            </a>
            <a href="{{ route('login') }}"
               class="btn-primary flex items-center gap-2 text-sm shadow-md hover:shadow-lg">
                Try it free
            </a>
        </div>

        {{-- Mobile Menu Button (Placeholder for JS functionality) --}}
        <button class="md:hidden text-slate-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</header>
