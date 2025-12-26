<?php

namespace Modules\Logistics\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Logistics\Models\HSCode;

class HSCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $codes = [
            [
                'code' => '8703.22.90',
                'description' => 'Motor vehicles for transport of persons (1000cc - 1500cc)',
                'tariff_rate' => 35.00,
                'excise_rate' => 30.00,
                'vat_rate' => 15.00,
                'surtax_rate' => 10.00,
                'withholding_rate' => 3.00,
            ],
            [
                'code' => '8703.23.90',
                'description' => 'Motor vehicles for transport of persons (1500cc - 3000cc)',
                'tariff_rate' => 35.00,
                'excise_rate' => 60.00,
                'vat_rate' => 15.00,
                'surtax_rate' => 10.00,
                'withholding_rate' => 3.00,
            ],
            [
                'code' => '8471.30.00',
                'description' => 'Portable automatic data processing machines (Laptops)',
                'tariff_rate' => 0.00,
                'excise_rate' => 0.00,
                'vat_rate' => 15.00,
                'surtax_rate' => 10.00,
                'withholding_rate' => 3.00,
            ],
            [
                'code' => '8517.13.00',
                'description' => 'Smartphones',
                'tariff_rate' => 10.00,
                'excise_rate' => 0.00,
                'vat_rate' => 15.00,
                'surtax_rate' => 10.00,
                'withholding_rate' => 3.00,
            ],
            [
                'code' => '7214.20.00',
                'description' => 'Bars and rods of iron or non-alloy steel (Rebar)',
                'tariff_rate' => 20.00,
                'excise_rate' => 0.00,
                'vat_rate' => 15.00,
                'surtax_rate' => 10.00,
                'withholding_rate' => 3.00,
            ],
             [
                'code' => '3004.90.00',
                'description' => 'Medicaments (Pharmaceuticals)',
                'tariff_rate' => 5.00,
                'excise_rate' => 0.00,
                'vat_rate' => 0.00,
                'surtax_rate' => 0.00,
                'withholding_rate' => 3.00,
            ]
        ];

        foreach ($codes as $code) {
            HSCode::firstOrCreate(['code' => $code['code']], $code);
        }
    }
}
