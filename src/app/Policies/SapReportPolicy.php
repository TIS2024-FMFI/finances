<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SapReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether a user can access a SAP report.
     *
     * @param  \App\Models\User  $user
     * the user whose request to authorize
     * @param  \App\Models\SapReport  $report
     * the SAP report the user is attempting to access
     * @return bool
     * true if the user is allowed to perform this operation, false otherwise
     */
    public function view(User $user, SapReport $report)
    {
        // Admin má prístup k akejkoľvek SAP správe
        if ($user->is_admin) {
            return true;
        }

        // Inak kontrolujeme, či používateľ má prístup k účtu spojenému so správou
        return $report->account->users->contains($user);
    }

    /**
     * Determine whether a user can create a SAP report.
     *
     * @param  \App\Models\User  $user
     * the user whose request to authorize
     * @param  \App\Models\Account  $account
     * the account under which the user is attempting to create the SAP report
     * @return bool
     * true if the user is allowed to perform this operation, false otherwise
     */
    public function create(User $user, Account $account)
    {
        // Admin môže vytvoriť SAP správu pre akýkoľvek účet
        if ($user->is_admin) {
            return true;
        }

        // Inak kontrolujeme, či používateľ má prístup k účtu
        return $user->accounts->contains($account);
    }

    /**
     * Determine whether a user can delete a SAP report.
     *
     * This method evaluates if the given user has the authorization to delete a specific SAP report.
     * Admin users are granted universal permission to delete any SAP report. Non-admin users must be associated
     * with the account linked to the SAP report to have the deletion rights.
     *
     * @param \App\Models\User $user The user whose authorization request is being evaluated.
     * @param \App\Models\SapReport $report The SAP report the user is attempting to delete.
     * @return bool Returns true if the user is authorized to delete the SAP report, false otherwise.
     */
    public function delete(User $user, SapReport $report)
    {
        // Grant permission to admin users to delete any SAP report.
        if ($user->is_admin) {
            return true;
        }

        // For non-admin users, check if the user is associated with the account linked to the SAP report.
        // The ownership is determined by checking if the user is part of the users collection of the account.
        // This ensures that only users who have a direct association with the account can delete its SAP reports.
        return $report->account->users->contains($user);
    }

}
