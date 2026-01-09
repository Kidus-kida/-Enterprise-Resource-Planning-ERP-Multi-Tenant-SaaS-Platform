@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')

    <div class="blob-bg bg-green-50 w-full h-[50vh] top-0 left-0 -z-10 blur-3xl opacity-60"></div>

    <main class="space-y-24 py-24">
        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 text-center space-y-6">
            <span class="inline-block py-1 px-3 rounded-full bg-brand-50 text-brand-600 font-semibold text-xs tracking-wider uppercase">Industries</span>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-900 tracking-tight">
                Built for <span class="text-brand font-hand">your business.</span>
            </h1>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto font-light leading-relaxed">
                Whether you run a retail store, a factory, or a service agency, our platform adapts to your unique workflow.
            </p>
        </section>

        {{-- Industries Grid --}}
        <section class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['name' => 'Services', 'desc' => 'Time tracking, projects, and invoicing for agencies.', 'icon' => 'fa-briefcase', 'color' => 'text-blue-500'],
                ['name' => 'Retail', 'desc' => 'POS, inventory, and loyalty programs for shops.', 'icon' => 'fa-store', 'color' => 'text-pink-500'],
                ['name' => 'Manufacturing', 'desc' => 'MRP, PLM, and quality control for factories.', 'icon' => 'fa-industry', 'color' => 'text-orange-500'],
                ['name' => 'eCommerce', 'desc' => 'Unified inventory and sales for online sellers.', 'icon' => 'fa-shopping-cart', 'color' => 'text-purple-500'],
                ['name' => 'Construction', 'desc' => 'Project management and field service for builders.', 'icon' => 'fa-hard-hat', 'color' => 'text-yellow-500'],
                ['name' => 'Education', 'desc' => 'Student management and eLearning for schools.', 'icon' => 'fa-graduation-cap', 'color' => 'text-green-500'],
            ] as $industry)
                <div class="glass-card p-8 rounded-3xl hover:-translate-y-2 transition-transform duration-300 group">
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas {{ $industry['icon'] }} {{ $industry['color'] }} text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-3">{{ $industry['name'] }}</h3>
                    <p class="text-slate-500 leading-relaxed">{{ $industry['desc'] }}</p>
                    <a href="#" class="inline-flex items-center gap-2 mt-6 text-sm font-bold text-slate-900 group-hover:text-brand transition-colors">
                        Learn more <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </a>
                </div>
            @endforeach
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
