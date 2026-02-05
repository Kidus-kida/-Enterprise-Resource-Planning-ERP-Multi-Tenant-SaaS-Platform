<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function files()
    {
        return $this->hasMany(File::class);
    }


    public function members()
    {
        return $this->belongsToMany(User::class, 'member_folder');
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'member_folder')
                    ->wherePivot('is_owner', true);
    }

    public function isOwnedBy(User $user)
    {
        return $this->owners->contains($user);
    }



    public function syncMembers(array $userIds)
    {
        MemberFolder::where('folder_id', $this->id)
            ->where('is_owner', false)
            ->delete();

        foreach ($userIds as $userId) {
            MemberFolder::firstOrCreate([
                'folder_id' => $this->id,
                'user_id' => $userId,
            ]);
        }
    }

}
