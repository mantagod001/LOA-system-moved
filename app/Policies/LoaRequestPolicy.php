<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LoaRequest;
use App\Models\User;

class LoaRequestPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        // Administrator gets full bypass for all abilities EXCEPT approve/reject/discontinue —
        // they are not part of the approval workflow and cannot act outside valid LOA statuses.
        if ($user->role->is(UserRole::Administrator)
            && ! in_array($ability, ['approve', 'reject', 'discontinue', 'markReturned'], true)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LoaRequest $loaRequest): bool
    {
        if ($user->role->is(UserRole::President)) {
            return $loaRequest->isExtension();
        }

        if (! $user->role->is(UserRole::DepartmentHead)) {
            return true;
        }

        return $user->department_id
            && $loaRequest->department_id
            && (int) $user->department_id === (int) $loaRequest->department_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LoaRequest $loaRequest): bool
    {
        return $this->view($user, $loaRequest);
    }

    public function delete(User $user, LoaRequest $loaRequest): bool
    {
        return $user->role->is(UserRole::Administrator);
    }

    public function restore(User $user, LoaRequest $loaRequest): bool
    {
        return false;
    }

    public function forceDelete(User $user, LoaRequest $loaRequest): bool
    {
        return false;
    }

    public function approve(User $user, LoaRequest $loaRequest): bool
    {
        return $loaRequest->canBeApprovedBy($user);
    }

    public function reject(User $user, LoaRequest $loaRequest): bool
    {
        return $loaRequest->canBeRejectedBy($user);
    }

    public function discontinue(User $user, LoaRequest $loaRequest): bool
    {
        return $loaRequest->canBeDiscontinuedBy($user);
    }

    public function markReturned(User $user, LoaRequest $loaRequest): bool
    {
        return $loaRequest->canBeMarkedReturnedBy($user);
    }
}
