<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


/**
 * A representation of an instance in the "users" table.
 */
class User extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Indicates if the model should be timestamped, using created_at and updated_at columns.
     *
     * @var mixed
     */
    public $timestamps = false;

    /**
     * Set the user's password.
     *
     * Note: This method also clears the password_change_required flag.
     *
     * @param string $password
     * the plain-text password that should be set as the new password
     * @return bool
     * true on success, false otherwise
     */
    public function setPassword(string $password)
    {
        $this->password = Hash::make($password);
        $this->password_change_required = false;

        return $this->save();
    }


    /**
     * Gets all the accounts which belong to this user.
     *
     * @return BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_user')->withPivot('id', 'account_title');
    }


    /**
     * Gets all the financial operations which belong to this user.
     *
     * @return HasManyThrough
     */
    public function financialOperations()
    {
        return $this->hasManyThrough(financialOperations::class, AccountUser::class);
    }
}
