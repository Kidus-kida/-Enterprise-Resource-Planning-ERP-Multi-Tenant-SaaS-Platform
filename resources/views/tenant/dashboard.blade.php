@extends('layouts.app', ['pageTitle' => 'Tenant Dashboard'])

@push('page-styles')
    <style>
        .tenant-dashboard-shell { gap: 1.25rem; }
        .tenant-hero { background: linear-gradient(135deg, rgba(255,155,68,0.16), rgba(255,155,68,0.04)); border: 1px solid rgba(255,155,68,0.18); }
        .tenant-stat-card { border: 0; border-radius: 1rem; overflow: hidden; transition: transform .2s ease, box-shadow .2s ease; }
        .tenant-stat-card:hover { transform: translateY(-3px); box-shadow: 0 0.75rem 1.5rem rgba(15, 23, 42, .1); }
        .tenant-stat-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; }
        .tenant-module-card { border: 1px solid rgba(15, 23, 42, .08); border-radius: 1rem; transition: transform .2s ease, box-shadow .2s ease; }
        .tenant-module-card:hover { transform: translateY(-2px); box-shadow: 0 0.75rem 1.5rem rgba(15, 23, 42, .08); }
        .tenant-activity-item { border-left: 3px solid var(--primary-color); padding-left: 0.9rem; }
    </style>
@endpush

@section('page-content')
    @php
        $businessName = $tenantModel?->business?->name ?? appBrandName();
        $displayName = trim(collect([
            auth()->user()?->firstname,
            auth()->user()?->middlename,
            auth()->user()?->lastname,
        ])->filter()->join(' ')) ?: (auth()->user()?->username ?? auth()->user()?->email ?? __('User'));
        $greeting = now()->hour < 12 ? __('Good Morning') : (now()->hour < 18 ? __('Good Afternoon') : __('Good Evening'));
        $logoUrl = brand('logo') ?? brand('dark_logo');
        $stats = $dashboardData['stats'] ?? [];
        $activityItems = $dashboardData['activityItems'] ?? [];
        $growthLabels = $dashboardData['growthLabels'] ?? [];
        $growthValues = $dashboardData['growthValues'] ?? [];
        $primaryStats = [
            ['label' => __('Employees'), 'value' => number_format($stats['employees'] ?? 0), 'icon' => 'fa-user-group', 'color' => 'linear-gradient(135deg, #4f46e5, #6366f1)', 'detail' => __('People & roles')],
            ['label' => __('Departments'), 'value' => number_format($stats['departments'] ?? 0), 'icon' => 'fa-sitemap', 'color' => 'linear-gradient(135deg, #0f766e, #14b8a6)', 'detail' => __('Teams')],
            ['label' => __('Attendance Today'), 'value' => number_format($stats['attendance_today'] ?? 0), 'icon' => 'fa-calendar-check', 'color' => 'linear-gradient(135deg, #0ea5e9, #38bdf8)', 'detail' => __('Live check-ins')],
            ['label' => __('Leave Requests'), 'value' => number_format($stats['leave_requests'] ?? 0), 'icon' => 'fa-umbrella-beach', 'color' => 'linear-gradient(135deg, #d97706, #f59e0b)', 'detail' => __('Pending approvals')],
        ];
        $secondaryStats = [
            ['label' => __('Customers'), 'value' => number_format($stats['customers'] ?? 0), 'icon' => 'fa-users', 'color' => 'linear-gradient(135deg, #7c3aed, #a78bfa)', 'detail' => __('Client base')],
            ['label' => __('Products'), 'value' => number_format($stats['products'] ?? 0), 'icon' => 'fa-box-open', 'color' => 'linear-gradient(135deg, #be185d, #ec4899)', 'detail' => __('Catalog')],
            ['label' => __('Inventory'), 'value' => number_format($stats['inventory'] ?? 0), 'icon' => 'fa-cubes', 'color' => 'linear-gradient(135deg, #2563eb, #60a5fa)', 'detail' => __('Stock health')],
            ['label' => __('Sales'), 'value' => number_format($stats['sales'] ?? 0), 'icon' => 'fa-cart-shopping', 'color' => 'linear-gradient(135deg, #059669, #34d399)', 'detail' => __('Transactions')],
        ];
        $financeStats = [
            ['label' => __('Purchases'), 'value' => number_format($stats['purchases'] ?? 0), 'icon' => 'fa-truck', 'color' => 'linear-gradient(135deg, #b45309, #f59e0b)', 'detail' => __('Suppliers')],
            ['label' => __('Revenue'), 'value' => number_format($stats['revenue'] ?? 0, 2), 'icon' => 'fa-chart-line', 'color' => 'linear-gradient(135deg, #1d4ed8, #60a5fa)', 'detail' => __('This period')],
            ['label' => __('Expenses'), 'value' => number_format($stats['expenses'] ?? 0, 2), 'icon' => 'fa-wallet', 'color' => 'linear-gradient(135deg, #dc2626, #fb7185)', 'detail' => __('Operating costs')],
        ];
        $quickActions = [
            ['label' => __('Add Employee'), 'icon' => 'fa-user-plus', 'route' => Route::has('employees.index') ? route('employees.index') : null],
            ['label' => __('Create Payroll'), 'icon' => 'fa-file-invoice-dollar', 'route' => Route::has('payroll.processing.index') ? route('payroll.processing.index') : null],
            ['label' => __('Attendance'), 'icon' => 'fa-calendar-check', 'route' => Route::has('attendances.index') ? route('attendances.index') : null],
            ['label' => __('Customers'), 'icon' => 'fa-users', 'route' => Route::has('contacts.index') ? route('contacts.index') : null],
            ['label' => __('Products'), 'icon' => 'fa-box-open', 'route' => Route::has('products.index') ? route('products.index') : null],
            ['label' => __('Reports'), 'icon' => 'fa-chart-pie', 'route' => Route::has('reports.index') ? route('reports.index') : null],
        ];
        $moduleCards = collect([
            ['key' => 'dashboard', 'title' => __('Dashboard'), 'description' => __('Workspace overview and key metrics'), 'icon' => 'fa-table-columns', 'route' => route('tenant.dashboard', ['tenant' => $tenantSlug])],
            ['key' => 'hr', 'title' => __('HR'), 'description' => __('Employees, departments and org structure'), 'icon' => 'fa-user-group', 'route' => Route::has('employees.index') ? route('employees.index') : null],
            ['key' => 'payroll', 'title' => __('Payroll'), 'description' => __('Salary batches and payouts'), 'icon' => 'fa-file-invoice-dollar', 'route' => Route::has('payroll.processing.index') ? route('payroll.processing.index') : null],
            ['key' => 'crm', 'title' => __('CRM'), 'description' => __('Customer records and follow-up activity'), 'icon' => 'fa-address-book', 'route' => Route::has('contacts.index') ? route('contacts.index') : null],
            ['key' => 'inventory', 'title' => __('Inventory'), 'description' => __('Products, stock and availability'), 'icon' => 'fa-cubes', 'route' => Route::has('products.index') ? route('products.index') : null],
            ['key' => 'sales', 'title' => __('Sales'), 'description' => __('Sales transactions and opportunities'), 'icon' => 'fa-cart-shopping', 'route' => Route::has('sales.index') ? route('sales.index') : null],
            ['key' => 'purchases', 'title' => __('Purchases'), 'description' => __('Buy-side orders and suppliers'), 'icon' => 'fa-truck', 'route' => Route::has('purchase.index') ? route('purchase.index') : null],
            ['key' => 'accounting', 'title' => __('Accounting'), 'description' => __('Accounts, journals and reporting'), 'icon' => 'fa-calculator', 'route' => Route::has('accounts.index') ? route('accounts.index') : null],
        ])->filter(function ($module) use ($enabledModules) {
            if ($module['key'] === 'dashboard') {
                return true;
            }

            if (empty($enabledModules)) {
                return false;
            }

            return collect($enabledModules)->contains(function ($enabledModule) use ($module) {
                return str_contains(strtolower((string) $enabledModule), strtolower((string) $module['key'])) || strtolower((string) $enabledModule) === strtolower((string) $module['key']);
            });
        });
    @endphp

    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __('Tenant Dashboard') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard', ['tenant' => $tenantSlug]) }}">{{ __('Home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Workspace') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row tenant-dashboard-shell">
            <div class="col-12">
                <div class="card tenant-hero shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="{{ appBrandName() }}" style="max-height: 42px; max-width: 180px; object-fit: contain;">
                                    @endif
                                    <span class="badge bg-white text-dark">{{ __('Tenant Workspace') }}</span>
                                </div>
                                <h2 class="mb-2">{{ $greeting }},</h2>
                                <h4 class="mb-2 fw-semibold">{{ __('Welcome back') }}, {{ $displayName }}</h4>
                                <p class="text-muted mb-0 fs-5">{{ $businessName }}</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="bg-white rounded-4 p-4 shadow-sm">
                                    <p class="text-muted mb-1">{{ __('Current date') }}</p>
                                    <h5 class="mb-2 fw-semibold">{{ now()->translatedFormat('l, F j, Y') }}</h5>
                                    <p class="text-muted mb-0">{{ __('Current time') }} {{ now()->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($primaryStats as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="card tenant-stat-card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="tenant-stat-icon" style="background: {{ $stat['color'] }}">
                                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                                </div>
                                <span class="text-muted small">{{ $stat['detail'] }}</span>
                            </div>
                            <h3 class="mb-1 fw-semibold">{{ $stat['value'] }}</h3>
                            <p class="text-muted mb-0">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mt-1">
            @foreach($secondaryStats as $stat)
                <div class="col-xl-3 col-md-6">
                    <div class="card tenant-stat-card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="tenant-stat-icon" style="background: {{ $stat['color'] }}">
                                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                                </div>
                                <span class="text-muted small">{{ $stat['detail'] }}</span>
                            </div>
                            <h3 class="mb-1 fw-semibold">{{ $stat['value'] }}</h3>
                            <p class="text-muted mb-0">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mt-1">
            @foreach($financeStats as $stat)
                <div class="col-xl-4 col-md-6">
                    <div class="card tenant-stat-card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="tenant-stat-icon" style="background: {{ $stat['color'] }}">
                                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                                </div>
                                <span class="text-muted small">{{ $stat['detail'] }}</span>
                            </div>
                            <h3 class="mb-1 fw-semibold">{{ $stat['value'] }}</h3>
                            <p class="text-muted mb-0">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mt-2">
            <div class="col-xl-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">{{ __('Employee growth') }}</h5>
                                <p class="text-muted mb-0">{{ __('Recent monthly activity') }}</p>
                            </div>
                            <span class="badge bg-light text-dark">{{ __('Live data') }}</span>
                        </div>
                        <canvas id="tenantGrowthChart" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">{{ __('Quick Actions') }}</h5>
                                <p class="text-muted mb-0">{{ __('Shortcut cards') }}</p>
                            </div>
                        </div>
                        <div class="row g-2">
                            @foreach($quickActions as $action)
                                <div class="col-6">
                                    @if(!empty($action['route']))
                                        <a href="{{ $action['route'] }}" class="text-decoration-none">
                                            <div class="border rounded-4 p-3 h-100 text-center hover-shadow">
                                                <i class="fa-solid {{ $action['icon'] }} mb-2 text-primary"></i>
                                                <div class="small fw-semibold text-dark">{{ $action['label'] }}</div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="border rounded-4 p-3 h-100 text-center text-muted">
                                            <i class="fa-solid {{ $action['icon'] }} mb-2"></i>
                                            <div class="small fw-semibold">{{ $action['label'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-xl-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">{{ __('Module Launcher') }}</h5>
                                <p class="text-muted mb-0">{{ __('Available modules for this workspace') }}</p>
                            </div>
                        </div>
                        <div class="row g-3">
                            @forelse($moduleCards as $module)
                                <div class="col-md-6">
                                    @if(!empty($module['route']))
                                        <a href="{{ $module['route'] }}" class="text-decoration-none text-reset">
                                            <div class="tenant-module-card p-3 h-100">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="tenant-stat-icon" style="background: linear-gradient(135deg, var(--primary-color), #f59e0b);">
                                                        <i class="fa-solid {{ $module['icon'] }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold">{{ $module['title'] }}</h6>
                                                        <p class="text-muted mb-0 small">{{ $module['description'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <div class="tenant-module-card p-3 h-100">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="tenant-stat-icon" style="background: linear-gradient(135deg, #64748b, #94a3b8);">
                                                    <i class="fa-solid {{ $module['icon'] }}"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-semibold">{{ $module['title'] }}</h6>
                                                    <p class="text-muted mb-0 small">{{ $module['description'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="border rounded-4 p-4 text-center text-muted">
                                        {{ __('No enabled modules are available for this workspace yet.') }}
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">{{ __('Recent activity') }}</h5>
                                <p class="text-muted mb-0">{{ __('Latest tenant events') }}</p>
                            </div>
                        </div>
                        @if(!empty($activityItems))
                            <div class="d-flex flex-column gap-3">
                                @foreach($activityItems as $activity)
                                    <div class="tenant-activity-item">
                                        <div class="fw-semibold">{{ $activity['title'] }}</div>
                                        <div class="small text-muted">{{ $activity['subtitle'] }}</div>
                                        <div class="small text-muted">{{ $activity['time'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="border rounded-4 p-4 text-center text-muted">
                                {{ __('No recent activity has been recorded for this workspace yet.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('tenantGrowthChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($growthLabels),
                    datasets: [{
                        label: '{{ __('Users') }}',
                        data: @json($growthValues),
                        borderColor: '#ff9b44',
                        backgroundColor: 'rgba(255, 155, 68, 0.16)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        });
    </script>
@endpush
