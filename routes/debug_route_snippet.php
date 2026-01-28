<?php

use Illuminate\Support\Facades\Route;

Route::get('/debug-permissions', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }

    $user = auth()->user();
    
    // Force reload of relations
    $user->load('roles', 'permissions');
    
    echo "<h1>Debug Info</h1>";
    echo "<b>Current DB Connection:</b> " . \DB::connection()->getDatabaseName() . "<br>";
    echo "<b>User ID:</b> " . $user->id . "<br>";
    echo "<b>Name:</b> " . $user->name . "<br>";
    echo "<b>Email:</b> " . $user->email . "<br>";
    echo "<b>Type:</b> " . $user->type . "<br>";
    echo "<b>Business ID:</b> " . $user->business_id . "<br>";
    
    echo "<h3>Roles:</h3>";
    foreach($user->roles as $role) {
        echo $role->name . " (Guard: {$role->guard_name})<br>";
    }
    
    echo "<h3>Check Specific Permission: business_settings.access</h3>";
    $can = $user->can('business_settings.access') ? 'YES' : 'NO';
    echo "<b>Can Access?</b> " . $can . "<br>";

    echo "<h3>All Permissions via Roles:</h3>";
    try {
        $permissions = $user->getAllPermissions();
        foreach($permissions as $perm) {
            echo $perm->name . "<br>";
        }
    } catch (\Exception $e) {
        echo "Error fetching permissions: " . $e->getMessage();
    }
    
    echo "<h3>Session Data:</h3>";
    dump(session()->all());
});
