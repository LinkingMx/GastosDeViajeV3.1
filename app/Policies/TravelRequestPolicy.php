<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_travel::request');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('view_travel::request');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_travel::request');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('update_travel::request');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('delete_travel::request');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_travel::request');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('force_delete_travel::request');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_travel::request');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('restore_travel::request');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_travel::request');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TravelRequest $travelRequest): bool
    {
        return $user->can('replicate_travel::request');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_travel::request');
    }
}
