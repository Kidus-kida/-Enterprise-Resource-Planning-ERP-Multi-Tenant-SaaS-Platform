<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveManagementController extends Controller
{
    /**
     * Redirect to My Time (default landing page)
     */
    public function index()
    {
        return redirect()->route('leave.my-time');
    }

    /**
     * My Time - Employee's personal leave dashboard
     */
    public function myTime()
    {
        $pageTitle = __('My Time');
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Balances
        $allocations = \App\Models\LeaveAllocation::where('user_id', $user->id)
            ->where('year', date('Y'))
            ->with(['leaveType'])
            ->get();
            
        // Recent Requests (last 5)
        $requests = \App\Models\LeaveRequest::where('employee_id', $user->id)
            ->with(['leaveType'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Upcoming Holidays (next 3)
        $holidays = \App\Models\Holiday::where('startDate', '>=', now())
            ->orderBy('startDate')
            ->take(3)
            ->get();
            
        return view('leave.my-time.index', compact('pageTitle', 'allocations', 'requests', 'holidays'));
    }

    /**
     * Overview - Team/Manager view
     */
    public function overview()
    {
        $pageTitle = __('Overview');
        return view('leave.overview.index', compact('pageTitle'));
    }

    /**
     * Management - Admin functions
     */
    public function management()
    {
        $pageTitle = __('Management');
        return view('leave.management.index', compact('pageTitle'));
    }

    /**
     * Reporting - Analytics and reports
     */
    public function reporting()
    {
        $pageTitle = __('Reporting');
        
        // Summary Stats
        $stats = [
            'total_requests' => \App\Models\LeaveRequest::count(),
            'approved' => \App\Models\LeaveRequest::where('status', 'approved')->count(),
            'pending' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'rejected' => \App\Models\LeaveRequest::where('status', 'rejected')->count(),
        ];

        // Leave Type Utilization
        $utilization = \App\Models\LeaveType::withCount(['leaveRequests as days_used' => function($query) {
            $query->where('status', 'approved'); // Count processed requests
        }])->get();

        // Recent Activity
        $recent_activity = \App\Models\LeaveRequest::with(['user', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('leave.reporting.index', compact('pageTitle', 'stats', 'utilization', 'recent_activity'));
    }

    /**
     * Configuration - Settings landing page
     */
    public function configuration()
    {
        $pageTitle = __('Configuration');
        return view('leave.configuration.index', compact('pageTitle'));
    }

    /**
     * Configuration stub routes (will be implemented later)
     */
    public function timeOffTypes()
    {
        return redirect()->route('leave.configuration')->with('info', __('Time Off Types feature coming soon'));
    }

    public function accrualPlans()
    {
        return redirect()->route('leave.configuration')->with('info', __('Accrual Plans feature coming soon'));
    }

    public function publicHolidays()
    {
        // This will redirect to the enhanced holiday management
        return redirect()->route('leave.configuration')->with('info', __('Public Holidays being enhanced'));
    }

    public function mandatoryDays()
    {
        return redirect()->route('leave.configuration')->with('info', __('Mandatory Days feature coming soon'));
    }
}
