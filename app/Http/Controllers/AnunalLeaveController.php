<?php

namespace App\Http\Controllers;

use App\Models\AnunalLeave;
use Illuminate\Http\Request;
use Auth;
use App\DataTables\AnnualLeaveDataTable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;      // adjust namespace if different

class AnunalLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AnnualLeaveDataTable $dataTable)
    {

        $pageTitle = __("Leave Request");
        return $dataTable->render('pages.Annualleave.index', compact(
            'pageTitle'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // spelling kept to match your table

    public function store(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        // 2. Policy constants
        $PER_YEAR = 16;
        $PER_MONTH = round($PER_YEAR / 12, 2);     // 1.33 per month
        $now = Carbon::now();


        DB::transaction(function () use ($year, $PER_YEAR, $PER_MONTH, $now) {

            User::query()
                ->each(function ($employee) use ($year, $PER_YEAR, $PER_MONTH, $now) {

                    $prevRecord = AnunalLeave::where('employee_id', $employee->id)
                        ->where('year_bpy', $year - 1)
                        ->first();

                    $previousYearDays = $prevRecord?->current_year ?? 0;
                    AnunalLeave::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'year_bpy' => $year,
                        ],
                        [
                            'current_year' => $PER_YEAR,
                            'previous_year' => $previousYearDays,
                            'per_month' => $PER_MONTH,
                            'per_year' => $PER_YEAR,
                            'total_anunal_leave' => $PER_YEAR + $previousYearDays,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                });
        });

        return back()->with(
            notify(__('Annual‑leave balances generated for :year', ['year' => $year]))
        );
    }


    /**
     * Display the specified resource.
     */
    public function show(AnunalLeave $anunalLeave)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnunalLeave $anunalLeave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnunalLeave $anunalLeave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnunalLeave $anunalLeave)
    {
        //
    }
}
