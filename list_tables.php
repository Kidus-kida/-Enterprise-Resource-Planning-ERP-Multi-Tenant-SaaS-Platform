<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
foreach(DB::select('SHOW TABLES') as $table) { 
    $name = array_values((array)$table)[0];
    if (str_contains($name, 'trans')) {
        echo $name . "\n"; 
    }
}
