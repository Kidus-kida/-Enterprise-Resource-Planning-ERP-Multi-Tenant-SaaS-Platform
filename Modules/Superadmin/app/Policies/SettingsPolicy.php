<?php

namespace Modules\Superadmin\Policies;

use App\Models\User;

/**
 * SettingsPolicy — only superadmins may access the settings module.
 */
class SettingsPolicy
{
    /**
     * Gate check: only superadmin role passes.
     */
    private function isSuperAdmin(User $user): bool
    {
        // Adjust to your role guard implementation
        return $user->hasRole('superadmin')
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin());
    }

    public function viewAny(User $user): bool   { return $this->isSuperAdmin($user); }
    public function view(User $user): bool       { return $this->isSuperAdmin($user); }
    public function create(User $user): bool     { return $this->isSuperAdmin($user); }
    public function update(User $user): bool     { return $this->isSuperAdmin($user); }
    public function delete(User $user): bool     { return $this->isSuperAdmin($user); }
    public function export(User $user): bool     { return $this->isSuperAdmin($user); }
    public function import(User $user): bool     { return $this->isSuperAdmin($user); }
    public function restore(User $user): bool    { return $this->isSuperAdmin($user); }
}
