<?php

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Total Users before: ' . DB::table('users')->count() . PHP_EOL;
DB::table('users')->delete();
echo 'Total Users after: ' . DB::table('users')->count() . PHP_EOL;
