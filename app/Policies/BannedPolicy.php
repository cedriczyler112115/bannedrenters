<?php

namespace App\Policies;

use App\Models\Banned;
use App\Models\User;

class BannedPolicy
{
    public function update(User $user, Banned $banned): bool
    {
        return true;
    }

    public function delete(User $user, Banned $banned): bool
    {
        return $user->is_admin || $user->id === $banned->created_by;
    }
}
