<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationType extends Model
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
     * Extends a query asking for operation types so that it demands only
     * the types which can be assigned to operations directly by users.
     *
     * @param Builder $query
     * the builder whose query to extend
     * @return Builder
     * the extended query builder
     */
    public function scopeUserAssignable(Builder $query): Builder
    {
        return $query->where('repayment', '=', false);
    }

    /**
     * Get the expense repayment type.
     *
     * @return OperationType
     */
    public static function getRepaymentExpense()
    {
        return OperationType::where('expense', '=', true)
                            ->where('repayment', '=', true)
                            ->first();
    }

    /**
     * Get the income repayment type.
     *
     * @return OperationType
     */
    public static function getRepaymentIncome()
    {
        return OperationType::where('expense', '=', false)
                            ->where('repayment', '=', true)
                            ->first();
    }
}
