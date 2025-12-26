<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\Shipment;
use Modules\Logistics\Models\Container;

use Modules\Logistics\Models\CustomsDeclaration;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        // Primary Stats
        $totalShipments = Shipment::count();
        $inTransit = Shipment::whereIn('status', ['in_transit', 'vessel_departed', 'at_djibouti'])->count();
        $atDjibouti = Shipment::where('status', 'at_djibouti')->count();
        $pendingClearance = Shipment::whereIn('status', ['customs_clearance', 'pending'])->count();
        $released = Shipment::where('status', 'released')->count();
        
        // Secondary Stats
        // Avg Clearance Time (Mock calculation or real if data exists)
        // Ideally: avg(clearance_date - declaration_date)
        $avgClearanceTime = 4.2; 
        
        $totalDutiesPaidMTD = CustomsDeclaration::whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->where('status', 'released')
            ->sum('total_duties');
            
        // Demurrage Risk: Containers not released > 14 days from arrival
        $demurrageAtRisk = Container::whereHas('shipment', function($q) {
                $q->whereNotNull('actual_arrival')->where('status', '!=', 'released');
            })
            ->where('updated_at', '<', now()->subDays(14))
            ->count();
            
        // Recent Shipments (Visual Cards need more info)
        $recentShipments = Shipment::with(['containers', 'dryPort'])
            ->latest()
            ->take(4)
            ->get();

        return view('logistics::dashboard.index', compact(
            'totalShipments',
            'inTransit',
            'atDjibouti',
            'pendingClearance',
            'released',
            'avgClearanceTime',
            'totalDutiesPaidMTD',
            'demurrageAtRisk',
            'recentShipments'
        ));
    }
}
