<?php
echo "Current directory: " . __DIR__ . "\n";
$path = __DIR__ . '/vendor/autoload.php';
echo "Trying to require: " . $path . "\n";

if (file_exists($path)) {
    echo "File exists!\n";
    require $path;
    echo "Require successful!\n";
} else {
    echo "File DOES NOT exist!\n";
    echo "Scandir output:\n";
    print_r(scandir(__DIR__ . '/vendor'));
}
