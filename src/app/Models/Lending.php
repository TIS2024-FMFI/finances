<?php

namespace App\Models;

use App\Exceptions\DatabaseException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lending extends Model
{
    use hasFactory;

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
    protected $guarded = [];

    /**
     * Whether the DB record for this model should have its ID set automatically according to the incrementing ID rules.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The relationships to eager load.
     *
     * @var string[]
     */

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'expected_date_of_return' => 'date',
    ];

    /**
     * Find a repayment associated with a loan.
     *
     * @param int $loanId
     * the id of the loan for which to find a repayment
     * @return Lending|null
     * the repayment or null if none was found
     */
    public static function findRepayment(int $loanId)
    {
        $repayment = Lending::where('id', '=', $loanId)->first();

        return $repayment;
    }

    /**
     * Get the operation with which this lending is associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operation(): BelongsTo
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_id', 'id');
    }

    public function operation_client_id(): BelongsTo
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_client_id', 'id');
    }

    public function operation_host_id(): BelongsTo
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_host_id', 'id');
    }



    public function host()
    {
        return $this->belongsTo(AccountUser::class, 'host_id');
    }

    public function client()
    {
        return $this->belongsTo(AccountUser::class, 'client_id');
    }

    /**
     * Get the financial operation associated with the lending.
     */
    public function operation_host()
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_host_id');
    }

    public function operation_client()
    {
        return $this->belongsTo(FinancialOperation::class, 'operation_client_id');
    }



}
