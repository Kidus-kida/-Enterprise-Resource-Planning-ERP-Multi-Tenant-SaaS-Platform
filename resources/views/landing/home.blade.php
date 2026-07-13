@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')

    {{-- Organic Background Blobs --}}
    <div class="blob-bg bg-brand-100 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob-bg bg-accent-soft w-[30rem] h-[30rem] rounded-full top-40 right-0 translate-x-1/3"></div>

    @php
        $heroIcons = [
            ['icon' => 'fa-users', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50', 'label' => 'CRM'],
            ['icon' => 'fa-file-invoice-dollar', 'color' => 'text-green-500', 'bg' => 'bg-green-50', 'label' => 'Sales'],
            ['icon' => 'fa-box-open', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50', 'label' => 'Stock'],
            ['icon' => 'fa-user-tie', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50', 'label' => 'HR'],
            ['icon' => 'fa-chart-line', 'color' => 'text-red-500', 'bg' => 'bg-red-50', 'label' => 'Accounting'],
            ['icon' => 'fa-calendar-alt', 'color' => 'text-teal-500', 'bg' => 'bg-teal-50', 'label' => 'Planning'],
        ];

        $solutions = [
            [
                'title' => 'Finance',
                'desc' => 'Invoicing, Expenses, and Accounting made simple.',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-100',
            ],
            [
                'title' => 'Sales',
                'desc' => 'CRM, POS, and Subscriptions to boost revenue.',
                'color' => 'text-green-600',
                'bg' => 'bg-green-100',
            ],
            [
                'title' => 'HR',
                'desc' => 'Recruiting, Employees, and Payroll in one place.',
                'color' => 'text-purple-600',
                'bg' => 'bg-purple-100',
            ],
            [
                'title' => 'Marketing',
                'desc' => 'Email Marketing, Automation, and Social Media.',
                'color' => 'text-pink-600',
                'bg' => 'bg-pink-100',
            ],
        ];
    @endphp

    <main class="relative z-10 space-y-32 pb-32">
        {{-- Hero Section --}}
        <section class="max-w-7xl mx-auto px-6 h-auto min-h-[90vh] grid lg:grid-cols-2 gap-12 items-center pt-24 lg:pt-0">
            <div class="space-y-8 relative">
                <span class="font-hand text-brand-600 text-2xl rotate-[-2deg] block mb-2">Unique Value Proposition</span>
                <h1 class="text-6xl md:text-7xl font-extrabold text-slate-900 leading-[1.1] tracking-tight">
                    All your business <br>
                    on <span class="marker-highlight">one platform.</span>
                </h1>
                <p class="text-xl text-slate-600 leading-relaxed max-w-lg font-light">
                    Simple, efficient, and affordable. The only software you need to run your business from top to bottom.
                </p>
                
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="{{ route('login') }}" class="btn-primary flex items-center gap-2 group">
                        Start now — It's Free
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>  
                    </a>
                    <button class="px-6 py-3 rounded-full border-2 border-slate-200 font-semibold hover:bg-white hover:border-slate-300 transition-colors">
                        Meet an Advisor
                    </button>
                </div>
                
                <div class="pt-8 flex items-center gap-2 text-sm text-slate-500 font-medium">
                   <span class="flex -space-x-2">
                       <div class="w-8 h-8 rounded-full bg-slate-200 border-2 border-white"></div>
                       <div class="w-8 h-8 rounded-full bg-slate-300 border-2 border-white"></div>
                       <div class="w-8 h-8 rounded-full bg-slate-400 border-2 border-white"></div>
                   </span>
                   <span class="ml-2">Trusted by 7M+ users worldwide</span>
                </div>

                {{-- Hand-drawn arrow --}}
                <div class="absolute -right-10 top-2/3 hidden md:block">
                     <svg width="100" height="60" viewBox="0 0 100 60" fill="none" stroke="#f05931" stroke-width="2" stroke-linecap="round" style="transform: rotate(15deg);">
                        <path d="M10,10 Q50,50 90,30" />
                        <path d="M80,25 L90,30 L85,40" />
                    </svg>
                    <span class="font-hand text-brand-600 text-lg absolute top-10 right-0 rotate-12">Amazing!</span>
                </div>
            </div>

            {{-- ERP Preview Card --}}
            <div class="relative mx-auto hidden h-[560px] w-full max-w-[520px] items-center justify-center lg:flex">
                <div class="absolute inset-8 rounded-[2.5rem] bg-gradient-to-br from-brand/15 via-white to-accent/10 blur-3xl"></div>
                <div class="relative w-full rounded-[2rem] border border-slate-200/70 bg-white/80 p-4 shadow-[0_35px_90px_-20px_rgba(15,23,42,0.25)] backdrop-blur-xl">
                    <div class="rounded-[1.6rem] bg-slate-950 p-5 text-white">
                        <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">
                            <span>Unified ERP workspace</span>
                            <span class="rounded-full bg-emerald-500/20 px-2 py-1 text-emerald-300">Live</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] uppercase tracking-[0.25em] text-slate-400">Today</p>
                                        <p class="mt-2 text-xl font-semibold">Revenue overview</p>
                                    </div>
                                    <span class="rounded-full bg-brand/20 px-3 py-1 text-sm font-semibold text-brand-200">+$24k</span>
                                </div>

                                <div class="mt-4 h-24 rounded-2xl bg-gradient-to-br from-brand/40 to-accent/30 p-3">
                                    <div class="flex h-full items-end justify-between gap-2">
                                        <div class="w-full rounded-t-xl bg-white/80" style="height: 35%"></div>
                                        <div class="w-full rounded-t-xl bg-white/80" style="height: 72%"></div>
                                        <div class="w-full rounded-t-xl bg-white/80" style="height: 56%"></div>
                                        <div class="w-full rounded-t-xl bg-white/80" style="height: 82%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach($heroIcons as $icon)
                                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-3 py-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $icon['bg'] }} {{ $icon['color'] }} text-lg font-bold">
                                            {{ substr($icon['label'], 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">{{ $icon['label'] }}</p>
                                            <p class="text-xs text-slate-400">Connected module</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-900">Automations</p>
                            <p class="mt-1 text-sm text-slate-500">Alerts, approvals, and updates in one place.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-900">Multi-entity ready</p>
                            <p class="mt-1 text-sm text-slate-500">Scale across locations and teams without friction.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Features Section with Wave --}}
        <section class="relative py-24 bg-white">
            <div class="absolute top-0 left-0 w-full overflow-hidden leading-0 transform rotate-180">
                <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block h-16 w-full fill-slate-50">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
                </svg>
            </div>

            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-20">
                    <span class="inline-block py-1 px-3 rounded-full bg-brand-50 text-brand-600 font-semibold text-xs tracking-wider uppercase mb-4">
                        Features
                    </span>
                    <h2 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6">
                        Unleash your <span class="text-brand font-hand text-5xl md:text-6xl">growth</span>
                    </h2>
                    <p class="text-lg text-slate-600 font-light">
                        No more painful integrations. Custom apps, distinct features, all working seamlessly together.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($solutions as $sol)
                        <div class="glass-card hover:bg-white p-6 rounded-3xl transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                            <div class="w-14 h-14 rounded-2xl mb-6 {{ $sol['bg'] }} flex items-center justify-center">
                                <span class="text-2xl {{ $sol['color'] }}">●</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $sol['title'] }}</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                {{ $sol['desc'] }}
                            </p>
                            <a href="#" class="mt-4 inline-block text-xs font-bold text-slate-900 border-b-2 border-brand/20 hover:border-brand transition-colors">
                                Explore →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Call to Action --}}
        <section class="max-w-5xl mx-auto px-6">
            <div class="bg-slate-900 rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand rounded-full blur-[100px] opacity-20"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-accent rounded-full blur-[100px] opacity-20"></div>
                
                <div class="relative z-10 space-y-8">
                    <h2 class="text-4xl md:text-6xl font-bold text-white tracking-tight">
                        Transform your business <br>
                        <span class="font-hand text-brand-200">today.</span>
                    </h2>
                    <p class="text-slate-400 max-w-xl mx-auto text-lg font-light">
                        Join the revolution of integrated business software. No credit card required.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('login') }}" class="btn-primary bg-white text-slate-900 hover:bg-slate-100 hover:text-slate-900 shadow-none border-0">
                            Get Started for Free
                        </a>
                        <button class="px-8 py-3 rounded-full text-white font-semibold hover:bg-white/10 transition-colors">
                            Schedule a Demo
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
