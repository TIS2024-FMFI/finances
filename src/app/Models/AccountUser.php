<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountUser extends Model
{
    use HasFactory;

    /**
     * The name of the table in the database.
     * 
     * @var string
     */
    protected $table = 'account_user';

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
     * Returns the user to which the record belongs to.
     * 
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the account to which the record belongs to.
     * 
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Returns all financial operations that belong to this record.
     * 
     * @return HasMany
     */
    public function financialOperations()
    {
        return $this->hasMany(FinancialOperation::class);
    }

}
