<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether a user can access an account.
     *
     * This method evaluates if the given user has the authorization to access a specific account.
     * Admin users are granted universal access to all accounts, reflecting their elevated privileges within the system.
     * Non-admin users must have a direct association with the account to be granted access, ensuring that users can only view accounts they are explicitly permitted to see.
     *
     * @param \App\Models\User $user The user whose authorization request is being evaluated.
     * @param \App\Models\Account $account The account the user is attempting to access.
     * @return bool Returns true if the user is authorized to access the account, false otherwise.
     */
    public function view(User $user, Account $account)
    {
        // Grant access to any account for admin users, acknowledging their elevated privileges.
        if ($user->is_admin) {
            return true;
        }

        // For non-admin users, check if the user is directly associated with the account.
        // This is determined by checking if the user is part of the account's users collection,
        // which represents the relationship between users and accounts.
        return $account->users->contains($user);
    }



    /**
     * Determine whether a user can update an account.
     *
     * Admin users are granted universal permission to update any account due to their elevated privileges.
     * Non-admin users must be directly associated with the account to have the permission to update it,
     * ensuring that users can only modify accounts they are explicitly permitted to manage.
     *
     * @param \App\Models\User $user The user whose authorization request is being evaluated.
     * @param \App\Models\Account $account The account the user is attempting to update.
     * @return bool Returns true if the user is authorized to update the account, false otherwise.
     */
    public function update(User $user, Account $account)
    {
        // Grant permission to update any account for admin users.
        if ($user->is_admin) {
            return true;
        }

        // For non-admin users, check if the user is directly associated with the account.
        return $user->accounts->contains($account);
    }



    /**
     * Determine whether a user can delete an account.
     *
     * This method evaluates if the given user has the authorization to delete a specific account.
     * Admin users are granted universal access to delete any accounts, reflecting their elevated privileges within the system.
     * Non-admin users must have a direct association with the account to be granted deletion rights,
     * ensuring that users can only delete accounts they are explicitly permitted to manage.
     *
     * @param \App\Models\User $user The user whose authorization request is being evaluated.
     * @param \App\Models\Account $account The account the user is attempting to delete.
     * @return bool Returns true if the user is authorized to delete the account, false otherwise.
     */
    public function delete(User $user, Account $account)
    {
        // Grant permission to delete any account for admin users.
        if ($user->is_admin) {
            return true;
        }

        // For non-admin users, check if the user is directly associated with the account.
        return $user->accounts->contains($account);
    }

}
