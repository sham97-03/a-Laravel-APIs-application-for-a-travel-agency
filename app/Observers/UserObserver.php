<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function creating(User $user): void
    {
        if ($user->password && ! Hash::needsRehash($user->password)) {
            $user->password = Hash::make($user->password);
        }
    }
}
