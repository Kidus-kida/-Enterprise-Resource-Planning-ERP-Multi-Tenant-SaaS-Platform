<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugTenantsCommand extends Command
{
    protected $signature = 'debug:tenants';
    protected $description = 'List tenant rows from the central database';

    public function handle(): int
    {
        $rows = DB::connection('mysql')->table('tenants')->get();

        if ($rows->isEmpty()) {
            $this->warn('No tenant rows found.');
            return 0;
        }

        $this->table(
            ['id', 'database_name', 'business_id'],
            $rows->map(fn ($row) => [
                $row->id,
                $row->database_name ?? null,
                $row->business_id ?? null,
            ])->all()
        );

        return 0;
    }
}
