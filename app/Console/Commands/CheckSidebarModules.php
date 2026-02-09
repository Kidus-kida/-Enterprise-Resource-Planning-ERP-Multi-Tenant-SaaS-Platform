<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSidebarModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:sidebar-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose sidebar module visibility issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Checking Central Modules Table ---');
        try {
            $modules = DB::table('modules')->orderBy('sort_order')->get();
            if ($modules->isEmpty()) {
                $this->error('No modules found in "modules" table!');
            } else {
                $headers = ['Name', 'Key', 'Active', 'Core'];
                $data = $modules->map(function($m) {
                    return [$m->name, $m->key, $m->is_active ? 'Yes' : 'No', $m->is_core ? 'Yes' : 'No'];
                })->toArray();
                $this->table($headers, $data);
            }
        } catch (\Exception $e) {
            $this->error("Error checking modules table: " . $e->getMessage());
        }

        $this->info("\n--- Checking Recent Subscriptions (Top 5) ---");
        try {
            $results = DB::table('subscriptions')
                ->join('businesses', 'subscriptions.business_id', '=', 'businesses.id')
                ->join('packages', 'subscriptions.package_id', '=', 'packages.id')
                ->orderBy('subscriptions.created_at', 'desc')
                ->select(
                    'businesses.name as business_name', 
                    'packages.name as package_name', 
                    'subscriptions.module_activation_details', 
                    'subscriptions.status',
                    'subscriptions.created_at'
                )
                ->limit(5)
                ->get();

            if ($results->isEmpty()) {
                $this->error('No subscriptions found.');
            } else {
                foreach ($results as $r) {
                    $this->info("Business: {$r->business_name} | Package: {$r->package_name} | Status: {$r->status} | Created: {$r->created_at}");
                    
                    
                    if (empty($r->module_activation_details)) {
                         $this->error("module_activation_details is NULL or EMPTY string!");
                    } else {
                        $this->line("Modules JSON Raw: " . substr($r->module_activation_details, 0, 100) . (strlen($r->module_activation_details) > 100 ? '...' : ''));
                        $details = json_decode($r->module_activation_details, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $this->error("JSON Error: " . json_last_error_msg());
                        } else {
                            if (is_array($details)) {
                                $enabled = [];
                                foreach ($details as $k => $v) {
                                    if ($v) $enabled[] = $k;
                                }
                                $this->comment("Enabled Keys Parsed: " . (empty($enabled) ? 'NONE' : implode(', ', $enabled)));
                            } else {
                                $this->error("JSON decoded but not an array.");
                            }
                        }
                    }
                    $this->line("--------------------------------------------------");
                }
            }
        } catch (\Exception $e) {
            $this->error("Error checking subscriptions: " . $e->getMessage());
        }
    }
}
