<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function owners($query)
    {
        return $query->where('is_owner', true);
    }

    public function members($query)
    {
        return $query->where('is_owner', false);
    }
}