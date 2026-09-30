<?php

namespace App\Policies;

use App\Models\LeadNote;
use App\Models\User;

/** Notes are the follow-up log: authors edit their own notes (admins any). */
class LeadNotePolicy
{
    public function update(User $user, LeadNote $note): bool
    {
        return $note->user_id === $user->id;
    }

    public function delete(User $user, LeadNote $note): bool
    {
        return $note->user_id === $user->id;
    }
}
