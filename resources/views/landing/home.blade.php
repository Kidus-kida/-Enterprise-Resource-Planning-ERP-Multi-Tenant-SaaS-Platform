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

            {{-- Floating Apps Grid --}}
            <div class="relative h-[600px] w-full hidden lg:block perspective-1000">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-50/50 to-transparent rounded-full blur-3xl"></div>
                
                {{-- Central Hub Circle --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-white rounded-3xl shadow-2xl flex items-center justify-center z-20 animate-pulse-slow">
                    <span class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-brand to-accent">T</span>
                </div>

                {{-- Orbiting Icons --}}
                @foreach($heroIcons as $index => $icon)
                    @php
                        // Calculate positions for a circular layout
                        $angle = ($index * (360 / count($heroIcons))) - 90;
                        $radius = 200; // Distance from center
                        $x = $radius * cos(deg2rad($angle));
                        $y = $radius * sin(deg2rad($angle));
                        $delay = $index * 1; // Staggered animation
                    @endphp
                    <div class="glass-card absolute p-4 rounded-2xl shadow-lg flex flex-col items-center gap-2 w-28 animate-float" 
                         style="top: calc(50% + {{ $y }}px); left: calc(50% + {{ $x }}px); transform: translate(-50%, -50%); animation-delay: {{ $delay }}s;">
                        <div class="w-12 h-12 rounded-xl {{ $icon['bg'] }} flex items-center justify-center text-xl {{ $icon['color'] }}">
                             {{-- Placeholder for FontAwesome icon, using text for now if not available --}}
                             <span class="font-bold">{{ substr($icon['label'], 0, 1) }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-700">{{ $icon['label'] }}</span>
                    </div>
                @endforeach
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
