<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Enums\UserType;
use App\Helpers\AppMenu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Expense;
use Modules\Sales\Models\Invoice;
use LaravelLang\LocaleList\Locale;
use Modules\Sales\Models\Estimate;
use Modules\Accounting\Models\Budget;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_DURATION = 300;

    public function index()
    {
        try {
            // Test database connection first
            try {
                DB::connection()->getPdo();
            } catch (Exception $dbError) {
                return response()->view('errors.database-connection', [
                    'error' => $dbError->getMessage(),
                    'config' => [
                        'host' => env('DB_HOST'),
                        'port' => env('DB_PORT'),
                        'database' => env('DB_DATABASE'),
                        'username' => env('DB_USERNAME')
                    ]
                ], 500);
            }
            
            $this->data['pageTitle'] = __('Dashboard');
            
            // Check user authentication
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Handle employee dashboard
            if ($user->type === UserType::EMPLOYEE) {
                return view('pages.employees.dashboard', $this->data);
            }

            // For admin dashboard, use simplified data to avoid errors
            try {
                // Use caching for dashboard data with 5-minute expiry
                $dashboardData = Cache::remember('dashboard.data.' . auth()->id(), self::CACHE_DURATION, function () {
                    return $this->getDashboardDataSafe();
                });

                $this->data = array_merge($this->data, $dashboardData);
            } catch (\Exception $e) {
                // If dashboard data fails, use basic data
                \Log::error('Dashboard data error: ' . $e->getMessage());
                $this->data['error'] = 'Some dashboard data could not be loaded';
                $this->data = array_merge($this->data, $this->getBasicDashboardData());
            }
            
            return view('pages.dashboard', $this->data);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            
            // Return error view or redirect
            return response()->view('errors.dashboard-error', [
                'message' => 'Dashboard temporarily unavailable',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get basic dashboard data that should always work
     */
    private function getBasicDashboardData(): array
    {
        $data = [];
        
        try {
            $data['clientCount'] = User::where('type', UserType::CLIENT)->count();
            $data['employeeCount'] = User::where('type', UserType::EMPLOYEE)->count();
            $data['ticketCount'] = class_exists('App\Models\Ticket') ? \App\Models\Ticket::count() : 0;
        } catch (\Exception $e) {
            \Log::error('Basic dashboard data error: ' . $e->getMessage());
            $data['clientCount'] = 0;
            $data['employeeCount'] = 0;
            $data['ticketCount'] = 0;
        }
        
        return $data;
    }

    /**
     * Get dashboard data safely with error handling
     */
    private function getDashboardDataSafe(): array
    {
        // Try the original method first
        try {
            return $this->getDashboardData();
        } catch (\Exception $e) {
            \Log::error('Full dashboard data failed, using basic data: ' . $e->getMessage());
            return $this->getBasicDashboardData();
        }
    }

    /**
     * Get all dashboard data with optimized queries
     */
    private function getDashboardData(): array
    {
        $data = [];
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        
        $prevDate = Carbon::now()->subMonth();
        $prevYear = $prevDate->year;
        $prevMonth = $prevDate->month;

        // Projects - use count() instead of get()->count()
        $data['projects'] = null;
        $data['recentProjects'] = null;
        if (function_exists('module') && !empty(module('Project')) && module('Project')->isEnabled()) {
            try {
                $data['projects'] = \Modules\Project\Models\Project::count();
                $data['recentProjects'] = \Modules\Project\Models\Project::whereMonth('created_at', Carbon::today())->get();
            } catch (\Exception $e) {
                \Log::warning('Project module data error: ' . $e->getMessage());
                $data['projects'] = null;
                $data['recentProjects'] = null;
            }
        }

        // Use count() instead of get() for counting - MUCH faster
        $data['clientCount'] = User::where('type', UserType::CLIENT)->count();
        $data['thisMonthClientCount'] = User::where('type', UserType::CLIENT)
            ->whereMonth('created_at', Carbon::today())
            ->count();
        $data['employeeCount'] = User::where('type', UserType::EMPLOYEE)->count();
        $data['ticketCount'] = Ticket::count();

        // For backward compatibility, keep these as collections if views need them
        $data['clients'] = $data['clientCount'] > 0 ? User::where('type', UserType::CLIENT)->get() : null;
        $data['thisMonthClients'] = User::where('type', UserType::CLIENT)
            ->whereMonth('created_at', Carbon::today())
            ->get();
        $data['employees'] = $data['employeeCount'] > 0 ? User::where('type', UserType::EMPLOYEE)->get() : null;
        $data['tickets'] = $data['ticketCount'] > 0 ? Ticket::get() : null;

        // Sales module - OPTIMIZED: Use aggregated queries instead of loop
        if (function_exists('module') && module('Sales') && module('Sales')->isEnabled()) {
            try {
                // Current month vs previous month comparisons
                $data['thisMonthExpenses'] = Expense::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('amount');
                $data['prevMonthExpenses'] = Expense::whereMonth('created_at', $prevMonth)
                    ->whereYear('created_at', $prevYear)
                    ->sum('amount');
                
                $data['thisMonthEstimates'] = Estimate::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('grand_total');
                $data['prevMonthEstimates'] = Estimate::whereMonth('created_at', $prevMonth)
                    ->whereYear('created_at', $prevYear)
                    ->sum('grand_total');
                
                $data['thisMonthInvoices'] = Invoice::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->sum('grand_total');
                $data['prevMonthInvoices'] = Invoice::whereMonth('created_at', $prevMonth)
                    ->whereYear('created_at', $prevYear)
                    ->sum('grand_total');
                
                $data['invoiceCount'] = Invoice::count();
                // Get all invoices with just status for the statistics widget
                $data['invoices'] = Invoice::select('status')->get();
                
                $data['thisMonthInvoiceList'] = Invoice::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->get();
                $data['thisMonthPaidInvoiceList'] = Invoice::whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', '2')
                    ->get();

                // OPTIMIZED: Single aggregated queries instead of 12×4 = 48 queries
                $data['monthly_expense'] = Expense::selectRaw('MONTH(created_at) as month, SUM(amount) as total, COUNT(*) as count')
                    ->whereYear('created_at', $currentYear)
                    ->groupByRaw('MONTH(created_at)')
                    ->pluck('total', 'month')
                    ->toArray();
                
                $data['budget_collection'] = Budget::selectRaw('MONTH(created_at) as month, SUM(amount) as total, COUNT(*) as count')
                    ->whereYear('created_at', $currentYear)
                    ->groupByRaw('MONTH(created_at)')
                    ->pluck('total', 'month')
                    ->toArray();
                
                $data['invoice_collection'] = Invoice::selectRaw('MONTH(created_at) as month, SUM(grand_total) as total, COUNT(*) as count')
                    ->whereYear('created_at', $currentYear)
                    ->groupByRaw('MONTH(created_at)')
                    ->pluck('total', 'month')
                    ->toArray();
                
                $data['estimates_collection'] = Estimate::selectRaw('MONTH(created_at) as month, SUM(grand_total) as total, COUNT(*) as count')
                    ->whereYear('created_at', $currentYear)
                    ->groupByRaw('MONTH(created_at)')
                    ->pluck('total', 'month')
                    ->toArray();
            } catch (\Exception $e) {
                \Log::warning('Sales module data error: ' . $e->getMessage());
                // Set default values if sales module fails
                $data['thisMonthExpenses'] = 0;
                $data['prevMonthExpenses'] = 0;
                $data['thisMonthEstimates'] = 0;
                $data['prevMonthEstimates'] = 0;
                $data['thisMonthInvoices'] = 0;
                $data['prevMonthInvoices'] = 0;
                $data['invoiceCount'] = 0;
                $data['invoices'] = collect();
                $data['thisMonthInvoiceList'] = collect();
                $data['thisMonthPaidInvoiceList'] = collect();
                $data['monthly_expense'] = [];
                $data['budget_collection'] = [];
                $data['invoice_collection'] = [];
                $data['estimates_collection'] = [];
            }
        }

        // Accounting module
        $data['budgets'] = null;
        if (function_exists('module') && module('Accounting') && module('Accounting')->isEnabled()) {
            try {
                $data['budgets'] = Budget::count();
            } catch (\Exception $e) {
                \Log::warning('Accounting module data error: ' . $e->getMessage());
                $data['budgets'] = null;
            }
        }

        // Attendances - Optimize the absent query
        $data['absentees'] = User::where('type', UserType::EMPLOYEE)
            ->whereDoesntHave('attendances', function($query) {
                $query->whereDate('created_at', Carbon::today());
            })
            ->select('id', 'firstname', 'middlename', 'lastname', 'avatar', 'email')
            ->get();

        // Employee counts
        $data['thisMonthTotalEmployees'] = User::where('type', UserType::EMPLOYEE)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();
        $data['prevMonthTotalEmployees'] = User::where('type', UserType::EMPLOYEE)
            ->whereMonth('created_at', $prevMonth)
            ->whereYear('created_at', $prevYear)
            ->count();

        return $data;
    }
}

