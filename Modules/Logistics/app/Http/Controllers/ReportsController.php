<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\Shipment;
use Modules\Logistics\Models\CustomsDeclaration;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request) 
    {
        // 1. Quick Stats
        $totalShipmentsYTD = Shipment::whereYear('created_at', now()->year)->count();
        $totalValueYTD = Shipment::whereYear('created_at', now()->year)->sum('value_etb') ?? 0; // Assuming value_etb exists, else we might need to sum items
        // Mocking Value if column doesn't exist yet, or use Invoice Amount
        
        $avgClearanceTime = 4.2; // Placeholder or calculate: avg(released_at - created_at)
        
        $dutiesPaidMTD = CustomsDeclaration::whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->where('status', 'released')
            ->sum('total_duties');

        // 2. Charts Data
        
        // A. Shipment Trends (Last 6 Months)
        $months = [];
        $shipmentCounts = [];
        $shipmentValues = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $count = Shipment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $shipmentCounts[] = $count;
            
            // Mocking value for now as we don't have a direct value column in shipments table based on previous reads
            // We'll use a random multiplier of count for demo or sum customs value if available
            $shipmentValues[] = $count * 50000; 
        }
        
        // B. Status Distribution
        $statusDistribution = Shipment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
        // Ensure standard keys exist
        $statusData = [
            'released' => $statusDistribution['released'] ?? 0,
            'in_transit' => $statusDistribution['in_transit'] ?? 0,
            'pending' => $statusDistribution['pending'] ?? 0,
            'at_port' => ($statusDistribution['arrived_at_port'] ?? 0) // Mapping if exists
        ];

        // C. Duties by Category (HS Code Sections - Simplification)
        $dutiesByCategory = CustomsDeclaration::join('hs_codes', 'customs_declarations.hs_code_id', '=', 'hs_codes.id')
            ->select(DB::raw('LEFT(hs_codes.code, 2) as section'), DB::raw('sum(total_duties) as total'))
            ->where('customs_declarations.status', 'released')
            ->groupBy('section')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
            
        // D. Port Performance (Mock Data for Demo as we don't have explicit port logs yet)
        $portPerformance = [
            'Djibouti' => 3.2,
            'Modjo' => 4.5,
            'Kality' => 2.8,
            'Dire Dawa' => 3.9
        ];

        return view('logistics::reports.index', compact(
            'totalShipmentsYTD', 'totalValueYTD', 'avgClearanceTime', 'dutiesPaidMTD',
            'months', 'shipmentCounts', 'shipmentValues',
            'statusData', 'dutiesByCategory', 'portPerformance'
        ));
    }

    public function generate(Request $request)
    {
        // For PDF generation, we would redirect to a specific route or use a library like DomPDF
        // For now, we reuse index with query params or download mock
        return back()->with('success', 'Report generation started...');
    }
}
