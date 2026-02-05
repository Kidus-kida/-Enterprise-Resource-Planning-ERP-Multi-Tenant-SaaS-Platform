<?php

namespace Modules\Contacts\Models;

use App\Models\TenantModel;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends TenantModel
{
    //
    protected $guarded = ['id'];
}

