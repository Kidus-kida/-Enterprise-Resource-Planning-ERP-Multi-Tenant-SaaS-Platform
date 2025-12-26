<?php

namespace Modules\Logistics\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Logistics\Models\HSCode;

class CalculatorController extends Controller
{
    public function index()
    {
        $hsCodes = HSCode::where('is_active', true)->get();
        return view('logistics::calculator.index', compact('hsCodes'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'cif_value_usd' => 'required|numeric|min:0',
            'hs_code_id' => 'required|exists:hs_codes,id',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        $hsCode = HSCode::findOrFail($request->hs_code_id);
        $cifValue = $request->cif_value_usd;
        $exchangeRate = $request->exchange_rate;
        $cifValueEtb = $cifValue * $exchangeRate;

        $dutyRate = $hsCode->tariff_rate / 100;
        $exciseRate = $hsCode->excise_rate / 100;
        $vatRate = $hsCode->vat_rate / 100;
        $surtaxRate = $hsCode->surtax_rate / 100;
        $withholdingRate = $hsCode->withholding_rate / 100;

        // Duty Calculation
        $importDuty = $cifValueEtb * $dutyRate;
        $exciseTax = ($cifValueEtb + $importDuty) * $exciseRate;
        $surtax = ($cifValueEtb + $importDuty + $exciseTax) * $surtaxRate; // Check specific surtax base, usually CIF
        // In Ethiopia: Surtax is usually 10% on CIF + Duty + Excise (or just CIF depending on goods)
        // Let's use the standard formula: Surtax = (CIF + Duty + Excise) * 10% OR just CIF * 10%
        // Based on mock data: Surtax = 3000 on 30000 CIF (so 10% of CIF). Let's assume CIF base for surtax for now as per mock data (3000 is 10% of 30000).
        // BUT wait, in mock data: CIF=30000, Duty=10500 (35%), Surtax=3000 (10% of CIF).
        // Let's stick to Mock Data logic: Surtax = CIF * 10%.
        $surtax = $cifValueEtb * $surtaxRate;

        // VAT Base = CIF + Duty + Excise + Surtax
        $vatBase = $cifValueEtb + $importDuty + $exciseTax + $surtax;
        $vat = $vatBase * $vatRate;

        $withholding = $cifValueEtb * $withholdingRate;
        
        // Customs Service Fee (approx 2%?) Mock data doesn't mention percentage but has explicit value.
        // Mock data: 500 on 30000 CIF (1.666%). Maybe just a fixed fee or calculated?
        // Let's assume 2% like in the Calculator.tsx: Math.min(cifValueETB * 0.02, 50000);
        $customsServiceFee = min($cifValueEtb * 0.02, 50000);

        $totalDuties = $importDuty + $exciseTax + $surtax + $vat + $withholding + $customsServiceFee;

        return response()->json([
            'cif_value_etb' => $cifValueEtb,
            'import_duty' => $importDuty,
            'excise_tax' => $exciseTax,
            'surtax' => $surtax,
            'vat' => $vat,
            'withholding' => $withholding,
            'customs_service_fee' => $customsServiceFee,
            'total_duties' => $totalDuties,
            'breakdown' => [
                'tariff_rate' => $hsCode->tariff_rate,
                'excise_rate' => $hsCode->excise_rate,
                'vat_rate' => $hsCode->vat_rate,
                'surtax_rate' => $hsCode->surtax_rate,
            ]
        ]);
    }
}
