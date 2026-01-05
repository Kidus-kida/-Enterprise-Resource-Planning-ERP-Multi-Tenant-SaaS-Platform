@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')

    <div class="blob-bg bg-indigo-50 w-full h-[50vh] top-0 left-0 -z-10 blur-3xl opacity-60"></div>

    <main class="space-y-24 py-24">
        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 text-center space-y-6">
            <span class="inline-block py-1 px-3 rounded-full bg-brand-50 text-brand-600 font-semibold text-xs tracking-wider uppercase">Resources</span>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-900 tracking-tight">
                Empower your <span class="text-brand font-hand">team.</span>
            </h1>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto font-light leading-relaxed">
                Documentation, tutorials, and community support to help you get the most out of the platform.
            </p>
             
             {{-- Search Bar --}}
             <div class="max-w-2xl mx-auto relative group">
                 <input type="text" placeholder="Search documentation..." class="w-full pl-12 pr-6 py-4 rounded-full border border-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent transition-all group-hover:shadow-md">
                 <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
             </div>
        </section>

        {{-- Resources Grid --}}
        <section class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="glass-card p-8 rounded-3xl hover:shadow-xl transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-2xl mb-6">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">Documentation</h3>
                <p class="text-slate-500 mb-6">In-depth guides for every app and feature.</p>
                <ul class="space-y-3 mb-6">
                    <li><a href="#" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brand"><i class="fas fa-chevron-right text-xs"></i> Getting Started</a></li>
                    <li><a href="#" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brand"><i class="fas fa-chevron-right text-xs"></i> CRM Guide</a></li>
                     <li><a href="#" class="flex items-center gap-2 text-sm text-slate-600 hover:text-brand"><i class="fas fa-chevron-right text-xs"></i> Accounting</a></li>
                </ul>
                <a href="#" class="text-brand font-bold text-sm">View all docs →</a>
            </div>

            <div class="glass-card p-8 rounded-3xl hover:shadow-xl transition-all group">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600 text-2xl mb-6">
                    <i class="fas fa-play-circle"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">Tutorials</h3>
                <p class="text-slate-500 mb-6">Video walkthroughs to master key workflows.</p>
                <div class="relative rounded-xl overflow-hidden mb-4 group cursor-pointer">
                    <img src="https://via.placeholder.com/400x200" alt="Video Thumbnail" class="w-full object-cover">
                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-play text-white text-3xl"></i>
                    </div>
                </div>
                <a href="#" class="text-brand font-bold text-sm">Browse videos →</a>
            </div>

            <div class="glass-card p-8 rounded-3xl hover:shadow-xl transition-all group">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-2xl mb-6">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">Community</h3>
                <p class="text-slate-500 mb-6">Ask questions and share knowledge with other users.</p>
                 <div class="space-y-4">
                     <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                         <div class="text-xs text-slate-400 mb-1">Latest discussion</div>
                         <a href="#" class="font-semibold text-slate-800 hover:text-brand line-clamp-1">How to configure automatic email triggers?</a>
                     </div>
                      <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                         <div class="text-xs text-slate-400 mb-1">Latest discussion</div>
                         <a href="#" class="font-semibold text-slate-800 hover:text-brand line-clamp-1">Best practices for inventory valuation</a>
                     </div>
                 </div>
                <a href="#" class="text-brand font-bold text-sm mt-6 inline-block">Join Forum →</a>
            </div>
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
