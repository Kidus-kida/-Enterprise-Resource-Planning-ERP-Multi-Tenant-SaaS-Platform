<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Get the database connection for the model.
     * Dynamically uses 'tenant' connection when configured, otherwise uses default.
     *
     * @return string
     */
    public function getConnectionName()
    {
        if (!empty(config('database.connections.tenant'))) {
            return 'tenant';
        }
        return config('database.default');
    }
}
