@extends('layouts.landing')

@section('content')
    @include('landing.partials.header')
    
    {{-- Background Shapes --}}
    <div class="blob-bg bg-purple-100 w-[40rem] h-[40rem] rounded-full top-20 left-10 -z-10 blur-3xl opacity-50"></div>
    <div class="blob-bg bg-brand-50 w-96 h-96 rounded-full bottom-20 right-10 -z-10 blur-3xl opacity-50"></div>

    @php
        $appFamilies = [
            [
                'name' => 'Sales & CRM',
                'description' => 'Boost your sales, manage relationships, and close more deals.',
                'icon' => 'fa-bullhorn',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-50',
                'apps' => ['CRM', 'Point of Sale', 'Subscriptions', 'Rental', 'Sales'],
            ],
            [
                'name' => 'Finance',
                'description' => 'Everything you need to manage your financials in one place.',
                'icon' => 'fa-file-invoice-dollar',
                'color' => 'text-teal-600',
                'bg' => 'bg-teal-50',
                'apps' => ['Accounting', 'Invoicing', 'Expenses', 'Sign', 'Documents'],
            ],
            [
                'name' => 'Inventory & MRP',
                'description' => 'Maximize your warehouse efficiency and streamline manufacturing.',
                'icon' => 'fa-boxes',
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-50',
                'apps' => ['Inventory', 'Manufacturing', 'PLM', 'Purchase', 'Maintenance'],
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Centralize all your HR information and manage your employees.',
                'icon' => 'fa-users',
                'color' => 'text-pink-600',
                'bg' => 'bg-pink-50',
                'apps' => ['Employees', 'Recruitment', 'Time Off', 'Appraisals', 'Referrals'],
            ],
            [
                'name' => 'Marketing',
                'description' => 'Attract leads and engage with your audience.',
                'icon' => 'fa-paper-plane',
                'color' => 'text-indigo-600',
                'bg' => 'bg-indigo-50',
                'apps' => ['Social Marketing', 'Email Marketing', 'SMS Marketing', 'Events', 'Survey'],
            ],
            [
                'name' => 'Services',
                'description' => 'Manage your projects and track tasks easily.',
                'icon' => 'fa-project-diagram',
                'color' => 'text-red-600',
                'bg' => 'bg-red-50',
                'apps' => ['Project', 'Timesheets', 'Field Service', 'Helpdesk', 'Planning'],
            ],
        ];
    @endphp

    <main class="space-y-24 py-24">
        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 text-center space-y-6">
             <span class="inline-block py-1 px-3 rounded-full bg-brand-50 text-brand-600 font-semibold text-xs tracking-wider uppercase">
                App Store
            </span>
            <h1 class="text-5xl md:text-6xl font-bold text-slate-900 tracking-tight">
                One platform, <span class="text-brand font-hand">infinite possibilities.</span>
            </h1>
            <p class="text-xl text-slate-600 max-w-3xl mx-auto font-light leading-relaxed">
                No need for disjointed tools. Our integrated apps work seamlessly together to automate your business processes.
            </p>
        </section>

        {{-- Apps Grid --}}
        <section class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($appFamilies as $family)
                <div class="glass-card p-8 rounded-[2rem] hover:shadow-xl transition-all hover:-translate-y-1 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 {{ $family['bg'] }} rounded-bl-[100px] -mr-8 -mt-8 transition-transform group-hover:scale-150"></div>
                    
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl {{ $family['bg'] }} flex items-center justify-center mb-6">
                             {{-- Icon Placeholder --}}
                            <span class="{{ $family['color'] }} text-2xl font-bold">
                                {{ substr($family['name'], 0, 1) }}
                            </span>
                        </div>
                        
                        <h3 class="text-2xl font-bold text-slate-900 mb-3">{{ $family['name'] }}</h3>
                        <p class="text-slate-500 mb-6 leading-relaxed">
                            {{ $family['description'] }}
                        </p>
                        
                        <div class="flex flex-wrap gap-2">
                             @foreach ($family['apps'] as $app)
                                <span class="px-3 py-1 rounded-lg bg-white border border-slate-100 text-xs font-semibold text-slate-600 shadow-sm">
                                    {{ $app }}
                                </span>
                             @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Integration Section --}}
        <section class="max-w-7xl mx-auto px-6">
            <div class="bg-slate-900 rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden">
                 <div class="absolute inset-0 bg-gradient-to-r from-brand/20 to-accent/20"></div>
                 <div class="relative z-10 max-w-2xl mx-auto space-y-6">
                     <h2 class="text-3xl md:text-5xl font-bold text-white">Ready to streamline your work?</h2>
                     <p class="text-slate-300 text-lg">
                         Join the millions of users who rely on our ecosystem to drive their business forward.
                     </p>
                     <div class="pt-4">
                        <a href="{{ route('login') }}" class="btn-primary inline-flex bg-white text-slate-900 hover:bg-slate-100 hover:text-slate-900 border-none shadow-lg">
                            Get Started Free
                        </a>
                     </div>
                 </div>
            </div>
        </section>
    </main>

    @include('landing.partials.footer')
@endsection
