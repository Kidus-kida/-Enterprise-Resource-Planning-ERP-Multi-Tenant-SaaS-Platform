<?php

namespace App\Http\Controllers;

use App\Models\AnunalLeave;
use Illuminate\Http\Request;
use Auth;
use App\DataTables\AnnualLeaveDataTable;
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
    public function store(Request $request)
    {
        //
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
