<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Crm\Models\Lead;
use Modules\Crm\Models\FollowUp;
use Modules\Crm\Models\Campaign;

class ReportController extends Controller
{
    public function index()
    {
        $pageTitle = __('CRM Reports');
        
        // Lead statistics
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $convertedLeads = Lead::where('status', 'converted')->count();
        $lostLeads = Lead::where('status', 'lost')->count();
        
        // Follow-up statistics
        $totalFollowUps = FollowUp::count();
        $pendingFollowUps = FollowUp::where('status', 'pending')->count();
        $completedFollowUps = FollowUp::where('status', 'completed')->count();
        
        // Campaign statistics
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $completedCampaigns = Campaign::where('status', 'completed')->count();
        
        // Lead status breakdown
        $leadsByStatus = Lead::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        // Recent leads
        $recentLeads = Lead::latest()->take(5)->get();
        
        // Upcoming follow-ups
        $upcomingFollowUps = FollowUp::with('lead')
            ->where('status', 'pending')
            ->where('follow_up_date', '>=', now())
            ->orderBy('follow_up_date')
            ->take(5)
            ->get();
            
        // Recent campaigns
        $recentCampaigns = Campaign::latest()->take(5)->get();

        return view('crm::reports.index', compact(
            'pageTitle', 'totalLeads', 'newLeads', 'convertedLeads', 'lostLeads',
            'totalFollowUps', 'pendingFollowUps', 'completedFollowUps',
            'totalCampaigns', 'activeCampaigns', 'completedCampaigns',
            'leadsByStatus', 'recentLeads', 'upcomingFollowUps', 'recentCampaigns'
        ));
    }
}