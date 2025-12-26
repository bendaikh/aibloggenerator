<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Website;

class WebsitePolicy
{
    /**
     * Determine whether the user can view the website.
     */
    public function view(User $user, Website $website): bool
    {
        return $user->id === $website->user_id || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the website.
     */
    public function update(User $user, Website $website): bool
    {
        return $user->id === $website->user_id || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the website.
     */
    public function delete(User $user, Website $website): bool
    {
        return $user->id === $website->user_id || $user->isSuperAdmin();
    }
}

