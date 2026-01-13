<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sub3 = \Modules\Superadmin\Models\Subscription::with('business')->find(3);
$sub5 = \Modules\Superadmin\Models\Subscription::with('business')->find(5);

echo "=== Ownership Check ===\n";
if ($sub3) {
    echo "Sub #3 belongs to Business: [" . $sub3->business_id . "] " . ($sub3->business->name ?? 'UNKNOWN') . "\n";
}
if ($sub5) {
    echo "Sub #5 belongs to Business: [" . $sub5->business_id . "] " . ($sub5->business->name ?? 'UNKNOWN') . "\n";
}

if ($sub3->business_id == $sub5->business_id) {
    echo "\nCONCLUSION: Both subscriptions belong to the SAME Business.\n";
} else {
    echo "\nCONCLUSION: Different Businesses.\n";
}
