<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Article;

class ArticlePolicy
{
    /**
     * Determine whether the user can view the article.
     */
    public function view(User $user, Article $article): bool
    {
        return $user->id === $article->user_id || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the article.
     */
    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->user_id || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the article.
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id || $user->isSuperAdmin();
    }
}

