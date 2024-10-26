<?php

namespace App\Models;

use App\Http\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SapOperation extends Model
{
    use HasFactory;




    // Assuming your Excel file has columns like date, sum, title, etc.
    // Add these as fillable attributes in your model

    protected $fillable = ['date', 'sum', 'title', 'operation_type_id', 'subject', 'sap_id', 'account_sap_id'];

    // Rest of your model's code...


    /**
     * Name of the OperationType for SAP operations
     *
     * @var string
     */

    public static string $sap_operation_type_name = 'SAP výkaz';

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
     * The relationships to eager load.
     *
     * @var string[]
     */
    protected $with = [
        'operationType',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Extends a query asking for SAP operations so that it demands only
     * operations which represent expenses.
     *
     * @param Builder $query
     * the builder whose query to extend
     * @return Builder
     * the extended query builder
     */
    public function scopeExpenses(Builder $query): Builder
    {
        return $query
            ->join('operation_types', 'operation_type_id','=','operation_types.id')
            ->where('expense', '=', true);
    }

    /**
     * Extends a query asking for SAP operations so that it demands only
     * operations which represent income.
     *
     * @param Builder $query
     * the builder whose query to extend
     * @return Builder
     * the extended query builder
     */
    public function scopeIncomes(Builder $query): Builder
    {
        return $query
            ->join('operation_types', 'operation_type_id','=','operation_types.id')
            ->where('expense', '=', false);
    }

    /**
     * Returns the associated financial operation, if it exists.
     *
     * @return HasOne
     */
    public function financialOperation()
    {
        return $this->hasOne(FinancialOperation::class);
    }

    /**
     * Return true if the operation has an assiocated SAP operation
     * 
     * @return bool
     */
    public function isChecked()
    {
        return ! is_null($this->financialOperation);
    }

    /**
     * Returns the associated financial operation, if it exists.
     *
     * @return HasOne
     */
    public function account()
    {
        return $this->hasOne(Account::class, 'sap_id', 'account_sap_id');
    }

    /**
     * Returns the type of this operation.
     *
     * @return BelongsTo
     */
    public function operationType()
    {
        return $this->belongsTo(OperationType::class);
    }


    /**
     * Returns whether this operation is an expense (whether it represents a negative sum).
     *
     * @return bool
     */
    public function isExpense()
    {
        return $this->operationType->expense;
    }

    /**
     * Generates a string containing this operation's sum, with '-' after the number if it's an expense.
     *
     * @return string
     * the generated string
     */
    public function getSumString()
    {
        $sumString = sprintf('%.2f', $this->sum);
        if ($this->isExpense()) return "-$sumString";
        return $sumString;
    }

    /**
     * Generates an all-caps string describing whether this operation is checked,
     * or an empty string if this is a lending.
     *
     * @return string
     * the generated string
     */
    public function getCheckedString()
    {
        if ($this->isLending()) return '';
        return $this->isChecked() ? 'Áno' : 'Nie';
    }

    /**
     * Creates a string containing this operation's title with only alphanumeric characters and dashes.
     *
     * @return string
     * the transformed title
     */
    public function getSanitizedTitle()
    {
        return FileHelper::sanitizeString($this->title);
    }
}
