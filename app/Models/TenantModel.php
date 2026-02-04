<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'tenant';

    /**
     * Get the database connection for the model.
     * Overrides Model::getConnectionName to ensure strict strict tenant usage.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return 'tenant';
    }
}
