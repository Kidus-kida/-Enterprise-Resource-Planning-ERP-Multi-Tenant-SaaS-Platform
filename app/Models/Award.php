<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Award extends TenantModel
{
    use HasFactory;

    protected $table = 'awards';

    protected $fillable = [
        'user_id',
        'awarded_by',
        'title',
        'description',
        'award_type',
        'awarded_at',
        'award_file',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hr()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
