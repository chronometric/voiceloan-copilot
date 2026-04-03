<?php

namespace App\Policies;

use App\Models\Borrower;
use App\Models\User;

class BorrowerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Borrower $borrower): bool
    {
        return $user->id === $borrower->created_by_user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Borrower $borrower): bool
    {
        return $this->view($user, $borrower);
    }

    public function delete(User $user, Borrower $borrower): bool
    {
        return $this->view($user, $borrower);
    }
}
