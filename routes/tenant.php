<?php

use App\Contact;
use App\Http\Controllers\Auth\AuthController;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Product;
use App\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Modules\Superadmin\Models\Tenant;
use Spatie\Activitylog\Models\Activity;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('tenant.login', ['tenant' => request()->route('tenant')]);
    }

    return redirect()->route('tenant.dashboard', ['tenant' => request()->route('tenant')]);
});

Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('tenant.login', ['tenant' => request()->route('tenant')]);
    }

    $tenantSlug = request()->route('tenant');
    $tenantModel = null;
    $enabledModules = [];

    try {
        $tenantModel = Tenant::where('id', $tenantSlug)
            ->orWhere('database_name', $tenantSlug)
            ->first();

        if ($tenantModel && $tenantModel->business) {
            $enabledModules = $tenantModel->business->resolveEnabledModules();
        }
    } catch (\Throwable $e) {
        $enabledModules = [];
    }

    $dashboardData = Cache::remember('tenant.dashboard.' . ($tenantSlug ?? 'default') . '.' . ($tenantModel?->business_id ?? '0'), 300, function () use ($tenantModel, $tenantSlug) {
        $safeCount = function ($modelClass, ?callable $callback = null) {
            try {
                $query = $modelClass::query();
                if ($callback) {
                    $callback($query);
                }

                return (int) $query->count();
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $safeSum = function ($modelClass, string $column, ?callable $callback = null) {
            try {
                $query = $modelClass::query();
                if ($callback) {
                    $callback($query);
                }

                return (float) $query->sum($column);
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $stats = [
            'employees' => $safeCount(User::class),
            'departments' => $safeCount(Department::class),
            'attendance_today' => $safeCount(Attendance::class, function ($query) {
                $query->whereDate('startDate', today());
            }),
            'leave_requests' => $safeCount(LeaveRequest::class, function ($query) {
                $query->where('status', '!=', 'rejected');
            }),
            'customers' => $safeCount(Contact::class, function ($query) {
                $query->where('type', 'customer');
            }),
            'products' => $safeCount(Product::class),
            'inventory' => $safeCount(Product::class, function ($query) {
                $query->where('enable_stock', 1);
            }),
            'sales' => $safeCount(Transaction::class, function ($query) {
                $query->where('transaction_type', 'sell');
            }),
            'purchases' => $safeCount(Transaction::class, function ($query) {
                $query->where('transaction_type', 'purchase');
            }),
            'revenue' => $safeSum(Transaction::class, 'final_total', function ($query) {
                $query->where('transaction_type', 'sell');
            }),
            'expenses' => $safeSum(Transaction::class, 'final_total', function ($query) {
                $query->where('transaction_type', 'expense');
            }),
        ];

        $activityItems = [];
        try {
            $activityItems = Activity::query()
                ->latest()
                ->take(6)
                ->get()
                ->map(function ($activity) {
                    return [
                        'title' => $activity->description ?? __('Activity logged'),
                        'subtitle' => class_basename($activity->subject_type ?? 'System'),
                        'time' => $activity->created_at?->diffForHumans(),
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $activityItems = [];
        }

        $growthLabels = [];
        $growthValues = [];
        foreach (range(5, 0) as $monthOffset) {
            $month = now()->subMonths($monthOffset);
            $growthLabels[] = $month->translatedFormat('M');
            try {
                $growthValues[] = (int) User::query()
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->count();
            } catch (\Throwable $e) {
                $growthValues[] = 0;
            }
        }

        return compact('stats', 'activityItems', 'growthLabels', 'growthValues');
    });

    return view('tenant.dashboard', compact('tenantSlug', 'tenantModel', 'enabledModules', 'dashboardData'));
})->name('tenant.dashboard');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('tenant.login');
    Route::post('/login', 'loginAuth')->name('tenant.login.auth');
    Route::post('/logout', 'logout')->name('tenant.logout');

    Route::get('/forgot-password', 'forgotPassword')->name('tenant.password.email');
    Route::post('/forgot-password', 'sendResetLink')->name('tenant.password.request');
    Route::get('/reset-password/{token}', 'resetPassword')->name('tenant.password.reset');
    Route::post('/reset-password', 'updatePassword')->name('tenant.password.update');
});
