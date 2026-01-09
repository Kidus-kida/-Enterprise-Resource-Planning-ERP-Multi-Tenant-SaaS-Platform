<?php

use Modules\Superadmin\Models\Subscription;
use Modules\Superadmin\Models\Package;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$sub = Subscription::find(3);
$package = Package::find($sub->package_id);

echo "--- Subscription #3 Inspection ---\n";
echo "Package ID: " . $sub->package_id . "\n";
echo "Package Name: " . $package->name . "\n";
echo "Package Active Modules (from Package table): " . json_encode($package->module_activation_details ?? [], JSON_PRETTY_PRINT) . "\n";
echo "\n";
echo "Subscription Active Modules (from Subscription table): " . json_encode($sub->module_activation_details ?? [], JSON_PRETTY_PRINT) . "\n";

echo "\n--- Comparison ---\n";
if (json_encode($package->module_activation_details) === json_encode($sub->module_activation_details)) {
    echo "SUCCESS: Subscription matches Package exactly.\n";
} else {
    echo "FAIL: Mismatch detected.\n";
}
