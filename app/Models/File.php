<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'user_id',
        'title',
        'description',
        'path',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}

