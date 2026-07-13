@php
    $year = now()->year;
    $brandName = appBrandName();
    $brandShort = setting('whitelabel.short_name', $brandName);
    $brandLogo = brand('logo');
@endphp

<footer class="bg-white border-t border-slate-100 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-8 mb-12">
        <div class="space-y-4">
             <div class="flex items-center gap-2">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-8 w-auto max-w-[140px] object-contain">
                @else
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand to-accent text-white font-bold text-sm shadow-md">
                        M
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-900">
                        {{ $brandName }} <span class="text-brand">{{ $brandShort }}</span>
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 leading-relaxed">
                The all-in-one suite to manage your business. Efficient, affordable, and easy to use.
            </p>
        </div>

        <div>
            <h4 class="font-bold text-slate-900 mb-4">Services</h4>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><a href="#" class="hover:text-brand transition-colors">CRM</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Accounting</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Inventory</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">HR & Payroll</a></li>
            </ul>
        </div>
        
        <div>
            <h4 class="font-bold text-slate-900 mb-4">Company</h4>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><a href="#" class="hover:text-brand transition-colors">About Us</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Contact</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Jobs</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Partners</a></li>
            </ul>
        </div>

        <div>
             <h4 class="font-bold text-slate-900 mb-4">Connect</h4>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><a href="#" class="hover:text-brand transition-colors">LinkedIn</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Twitter</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">Facebook</a></li>
                <li><a href="#" class="hover:text-brand transition-colors">GitHub</a></li>
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center text-xs text-slate-400">
        <p>© {{ now()->year }} {{ appBrandName() }}. All Rights Reserved.</p>
        <div class="flex gap-4 mt-4 md:mt-0">
            <a href="#" class="hover:text-slate-600">Privacy Policy</a>
            <a href="#" class="hover:text-slate-600">Terms of Service</a>
        </div>
    </div>
</footer>
