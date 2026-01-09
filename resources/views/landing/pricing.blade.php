@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')

    {{-- Background --}}
    <div class="blob-bg bg-brand-100 w-[50rem] h-[50rem] rounded-full top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 blur-3xl opacity-40"></div>

    <main class="py-24 space-y-24">
        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 text-center space-y-6">
            <h1 class="text-5xl md:text-7xl font-bold text-slate-900 tracking-tight">
                Simple pricing. <br>
                <span class="text-brand font-hand">No surprises.</span>
            </h1>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto font-light leading-relaxed">
                Start for free, upgrade as you grow. All apps included in one simple price.
            </p>
            
            {{-- Free Toggle --}}
            <div class="inline-flex items-center gap-4 bg-white rounded-full p-2 shadow-sm border border-slate-200 mt-8">
                <span class="px-6 py-2 rounded-full bg-slate-900 text-white font-bold text-sm">One App Free</span>
                <span class="text-sm font-medium text-slate-500 pr-6">Unlimited users, forever.</span>
            </div>
        </section>

        {{-- Pricing Cards --}}
        <section class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8 items-center">
             {{-- Free Plan --}}
            <div class="glass-card p-8 rounded-[2rem] border border-white hover:border-brand-200 transition-all hover:shadow-xl relative group">
                <h3 class="text-2xl font-bold text-slate-900 mb-2">One App</h3>
                <div class="text-4xl font-bold text-brand mb-4">Free</div>
                <p class="text-sm text-slate-500 mb-8">Best for solo entrepreneurs or testing.</p>
                
                <ul class="space-y-4 mb-8 text-sm text-slate-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> One app included</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Unlimited users</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Community support</li>
                </ul>
                <a href="{{ route('login') }}" class="block w-full py-3 rounded-xl border-2 border-slate-200 text-center font-bold text-slate-700 hover:border-brand hover:text-brand transition-colors">Start Now</a>
            </div>

            {{-- Standard Plan (Highlighted) --}}
            <div class="bg-slate-900 p-10 rounded-[2.5rem] shadow-2xl relative transform md:-translate-y-4">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -mt-4 bg-gradient-to-r from-brand to-accent text-white px-6 py-1 rounded-full text-xs font-bold uppercase tracking-wide">Most Popular</div>
                
                <h3 class="text-2xl font-bold text-white mb-2">Standard</h3>
                <div class="flex items-baseline gap-1 mb-4">
                    <span class="text-5xl font-bold text-white">$24</span>
                    <span class="text-slate-400 text-sm">/user/month</span>
                </div>
                <p class="text-slate-400 mb-8 text-sm">For growing companies needing full power.</p>
                
                <ul class="space-y-4 mb-8 text-sm text-slate-300">
                    <li class="flex items-center gap-3"><span class="bg-green-500/20 text-green-400 rounded-full p-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span> All Apps Included</li>
                     <li class="flex items-center gap-3"><span class="bg-green-500/20 text-green-400 rounded-full p-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span> Multi-company</li>
                     <li class="flex items-center gap-3"><span class="bg-green-500/20 text-green-400 rounded-full p-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span> Standard Support</li>
                </ul>
                <a href="{{ route('login') }}" class="block w-full py-4 rounded-xl bg-white text-center font-bold text-slate-900 hover:bg-brand hover:text-white transition-all">Try Free for 15 Days</a>
            </div>

            {{-- Custom Plan --}}
            <div class="glass-card p-8 rounded-[2rem] border border-white hover:border-brand-200 transition-all hover:shadow-xl">
                <h3 class="text-2xl font-bold text-slate-900 mb-2">Custom</h3>
                <div class="text-4xl font-bold text-slate-900 mb-4">Let's Talk</div>
                <p class="text-sm text-slate-500 mb-8">For large organizations with specific needs.</p>
                
                <ul class="space-y-4 mb-8 text-sm text-slate-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Dedicated Manager</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Custom Development</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> On-premise Options</li>
                </ul>
                <a href="#" class="block w-full py-3 rounded-xl border-2 border-slate-200 text-center font-bold text-slate-700 hover:border-brand hover:text-brand transition-colors">Contact Sales</a>
            </div>
        </section>
        
        {{-- FAQ Section --}}
        <section class="max-w-4xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-slate-900 text-center mb-12">Frequently Asked Questions</h2>
            <div class="grid gap-6">
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="font-bold text-lg mb-2">Is the "One App Free" really free?</h3>
                    <p class="text-slate-600">Yes! If you only use one app (e.g. Invoicing), it is completely free for unlimited users. Forever.</p>
                </div>
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="font-bold text-lg mb-2">Can I switch plans later?</h3>
                    <p class="text-slate-600">Absolutely. You can upgrade or downgrade at any time. Your data is safe and secure.</p>
                </div>
                <div class="glass-card p-6 rounded-2xl">
                    <h3 class="font-bold text-lg mb-2">Do you offer discounts for non-profits?</h3>
                    <p class="text-slate-600">We love supporting good causes. Contact our sales team to discuss special pricing for NGOs and educational institutions.</p>
                </div>
            </div>
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
