<?php

use Modules\Superadmin\Models\Subscription;
use App\Business;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Checking subscriptions for Business ID 4...\n";
$subs = Subscription::where('business_id', 4)->get();

foreach ($subs as $sub) {
    echo "ID: {$sub->id} | Package: {$sub->package_id} | Status: {$sub->status} | Created: {$sub->created_at}\n";
    echo "Modules: " . json_encode($sub->module_activation_details) . "\n\n";
}

echo "Business->subscription returns ID: " . optional(Business::find(4)->subscription)->id . "\n";
