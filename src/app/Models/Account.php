<?php

namespace App\Models;

use App\Http\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class Account extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Array of attributes excluded from mass assignation.
     *
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * Returns all users who use this account.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'account_user')->withPivot('id', 'account_title');
    }

    /**
     * Returns the authentificated user of this account
     *
     * @return BelongsToMany
     */
    public function user()
    {
        return $this->users()->wherePivot('user_id', Auth::user()->id);
    }

    /**
     * Returns the authentificated user if the user is using this account
     *
     * @return BelongsToMany
     * The retured user,
     * returns null if the user is not using this account
     */
     public function accountUser()
     {
         return $this->users()->wherePivot('user_id', Auth::user()->id);
     }

    /**
     * Gets all financial operations belonging to this account.
     *
     * @return HasManyThrough
     */
    public function operations()
    {
        return $this->hasManyThrough(FinancialOperation::class, AccountUser::class);
    }

    /**
     * Gets all SAP operations belonging to this account.
     *
     * @return HasMany
     */
    public function sapOperations()
    {
        return $this->hasMany(SapOperation::class, 'account_sap_id', 'sap_id');
    }

    /**
     * Get all SAP reports associated with this account.
     *
     * @return HasMany
     */
    public function sapReports()
    {
        return $this->hasMany(SapReport::class);
    }

    /**
     * Get the account's SAP identifier in the form of a string consisting
     * only of alphanumeric characters and dash ('-') symbols.
     *
     * @return string
     * the transformed title
     */
    public function getSanitizedTitle()
    {
        return FileHelper::sanitizeString($this->title);
    }

    /**
     * Get the account's SAP identifier in the form of a string consisting
     * only of alphanumeric characters and dash ('-') symbols.
     *
     * @return string
     * the transformed SAP identifier
     */
    public function getSanitizedSapId()
    {
        return Str::replace('/', '-', $this->sap_id);
    }

    /**
     * Returns the balance of this account.
     *
     * @return float
     */
    public function getBalance()
    {
        $incomes = $this->operations()->incomes()->sum('sum');
        $expenses = $this->operations()->expenses()->sum('sum');

        return round($incomes - $expenses, 3);
    }

    /**
     * Builds a query requesting financial operations which belong to this account
     * and whose date is in the specified interval.
     *
     * @param Carbon $from
     * earliest date in the interval
     * @param Carbon $to
     * latest date in the interval
     * @return HasManyThrough the result query
     */
    public function operationsBetween(Carbon $from, Carbon $to)
    {
        return $this->operations()->whereBetween('date', [$from, $to]);
    }

    /**
     * Builds a query requesting financial operations
     * which belong to this account and to the specified user
     * and whose date is in the specified interval.
     *
     * This method is designed to filter financial operations for a given account based on the user's role and the specified date range.
     * Admin users are granted access to all operations within the account for the specified date range,
     * while non-admin users can only access operations that are directly associated with them within the same date range.
     *
     * @param User $user The user for whom the operations are being queried. This parameter determines the scope of the operations returned based on the user's role.
     * @param Carbon $from The start date of the interval for which operations are being requested. Only operations on or after this date are included.
     * @param Carbon $to The end date of the interval for which operations are being requested. Only operations on or before this date are included.
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough The query builder instance that can be used to further query the model or get the results.
     */
    public function userOperationsBetween(User $user, Carbon $from, Carbon $to)
    {
        // Grant access to all operations within the account for admin users.
        if ($user->is_admin) {
            return $this->operations()->whereBetween('date', [$from, $to]);
        }

        // For non-admin users, attempt to find the user within the account's users.
        // Use the null-safe operator to avoid errors if the user is not found.
        $accountUserId = $this->users()->where('users.id', '=', $user->id)->first()?->pivot?->id;

        // If the user is not associated with the account, return an empty collection to signify no operations are accessible.
        if ($accountUserId === null) {
            return collect(); // Alternatively, you could return an empty query or throw an exception.
        }

        // For non-admin users associated with the account, return their operations within the specified date range.
        return $this->operations()->where('account_user_id', $accountUserId)->whereBetween('date', [$from, $to]);
    }





    /**
     * Get all SAP reports which are associated with this account and which were
     * exported or uploaded within a specified period.
     *
     * @param \Illuminate\Support\Carbon $from
     * the date determining the beginning of the period to consider (inclusive)
     * @param \Illuminate\Support\Carbon $to
     * the date determining the end of the period to consider (inclusive)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sapReportsBetween(Carbon $from, Carbon $to)
    {
        return $this->sapReports()->whereBetween('exported_or_uploaded_on', [$from, $to]);
    }
}
