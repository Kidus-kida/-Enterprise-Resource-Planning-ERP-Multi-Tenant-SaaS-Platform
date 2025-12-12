<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\FixedAsset;
use Yajra\DataTables\Facades\DataTables;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $query = FixedAsset::query();
            
            if ($business_id) {
                $query->where('business_id', $business_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', function ($asset) {
                    return $asset->name ?? 'N/A';
                })
                ->addColumn('code', function ($asset) {
                    return $asset->code ?? 'N/A';
                })
                ->addColumn('purchase_date', function ($asset) {
                    return $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('purchase_cost', function ($asset) {
                    return number_format($asset->purchase_cost ?? 0, 2);
                })
                ->addColumn('current_value', function ($asset) {
                    return number_format($asset->current_value ?? 0, 2);
                })
                ->addColumn('status', function ($asset) {
                    return '<span class="badge bg-success">Active</span>';
                })
                ->addColumn('action', function ($asset) {
                    $actions = '<div class="dropdown dropdown-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="material-icons">more_vert</i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('fixed-asset.edit', $asset->id) . '">
                                <i class="fa-solid fa-pencil m-r-5"></i> Edit
                            </a>
                            <a class="dropdown-item" href="#" onclick="deleteAsset(' . $asset->id . ')">
                                <i class="fa-solid fa-trash m-r-5"></i> Delete
                            </a>
                        </div>
                    </div>';
                    return $actions;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('accounting::fixed-assets.index');
    }

    public function create()
    {
        return view('accounting::fixed-assets.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->route('fixed-asset.index');
    }

    public function show($id)
    {
        $asset = FixedAsset::findOrFail($id);
        return view('accounting::fixed-assets.show', compact('asset'));
    }

    public function edit($id)
    {
        $asset = FixedAsset::findOrFail($id);
        return view('accounting::fixed-assets.edit', compact('asset'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->route('fixed-asset.index');
    }

    public function destroy($id)
    {
        // TODO: Implement delete logic
        return redirect()->route('fixed-asset.index');
    }
}
