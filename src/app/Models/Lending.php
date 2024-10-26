<?php

namespace App\Models;

use App\Exceptions\DatabaseException;
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
    protected $with = [
        'repayment',
    ];

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
        return Lending::where('previous_lending_id', '=', $loanId)->first();
    }

    /**
     * Get the operation with which this lending is associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operation()
    {
        return $this->hasOne(FinancialOperation::class, 'id', 'id');
    }

    /**
     * Get the repayment associated with this loan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function repayment()
    {
        return $this->hasOne(Lending::class, 'previous_lending_id', 'id');
    }

    /**
     * Get the loan with which this lending is associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function loan()
    {
        return $this->hasOne(FinancialOperation::class, 'id', 'previous_lending_id');
    }

    /**
     * If this lending is repaid, deletes the repayment's record in the 'financial operations' DB table.
     * @return void
     * @throws DatabaseException
     */
    public function deleteRepayment()
    {
        $repayment = $this->repayment;
        if (! $repayment)
            return;
        if (! $repayment->operation->delete())
            throw new DatabaseException('The lending\'s repayment wasn\'t deleted.');
    }
}
