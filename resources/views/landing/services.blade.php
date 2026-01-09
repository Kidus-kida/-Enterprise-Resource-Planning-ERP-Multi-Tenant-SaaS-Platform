@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')

    <div class="blob-bg bg-blue-50 w-full h-[50vh] top-0 left-0 -z-10 blur-3xl opacity-60"></div>

    <main class="space-y-24 py-24">
        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 text-center space-y-6">
            <span class="inline-block py-1 px-3 rounded-full bg-brand-50 text-brand-600 font-semibold text-xs tracking-wider uppercase">Services</span>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-900 tracking-tight">
                Here to help you <span class="text-brand font-hand">succeed.</span>
            </h1>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto font-light leading-relaxed">
                From implementation to ongoing support, our experts are with you every step of the way.
            </p>
        </section>

        {{-- Services Grid --}}
        <section class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-8">
            @foreach([
                ['name' => 'Implementation', 'desc' => 'We help you configure your apps, import data, and train your team for a smooth launch.', 'icon' => 'fa-cogs', 'color' => 'text-blue-600'],
                ['name' => 'Customization', 'desc' => 'Need a specific feature? Our developers can build custom modules tailored to your needs.', 'icon' => 'fa-code', 'color' => 'text-purple-600'],
                ['name' => 'Training', 'desc' => 'On-site or remote training sessions to ensure your team masters the platform.', 'icon' => 'fa-chalkboard-teacher', 'color' => 'text-green-600'],
                ['name' => 'Support', 'desc' => '24/7 priority support to resolve any issues and keep your business running.', 'icon' => 'fa-headset', 'color' => 'text-red-600'],
            ] as $service)
                <div class="glass-card p-10 rounded-[2.5rem] flex gap-6 items-start hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center shrink-0">
                        <i class="fas {{ $service['icon'] }} {{ $service['color'] }} text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 mb-3">{{ $service['name'] }}</h3>
                        <p class="text-slate-500 leading-relaxed mb-4">
                            {{ $service['desc'] }}
                        </p>
                        <a href="#" class="text-sm font-bold text-brand hover:underline">Get a quote</a>
                    </div>
                </div>
            @endforeach
        </section>
        
        {{-- Partner Section --}}
        <section class="max-w-7xl mx-auto px-6 text-center">
            <div class="bg-slate-900 rounded-[3rem] p-12 relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="relative z-10 space-y-6">
                    <h2 class="text-3xl font-bold text-white">Become a Partner</h2>
                    <p class="text-slate-400 max-w-xl mx-auto">
                        Join our network of certified partners and help businesses transform their operations.
                    </p>
                    <button class="px-8 py-3 rounded-full bg-white text-slate-900 font-bold hover:bg-brand hover:text-white transition-colors">Apply Now</button>
                </div>
            </div>
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
