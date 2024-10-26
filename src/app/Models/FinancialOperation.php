<?php

namespace App\Models;

use App\Http\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\DB;
use App\Models\AccountUser;
use App\Models\Account;
use App\Models\User;


class FinancialOperation extends Model
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
     * The relationships to eager load.
     *
     * @var string[]
     */
    protected $with = [
        'operationType',
        'lending',
        'accountUser',
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
     * Extends a query asking for financial operations so that it demands only
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
     * Extends a query asking for financial operations so that it demands only
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
     * Extends a query asking for financial operations so that it demands only
     * operations which represent unrepaid lendings.
     *
     * @param Builder $query
     * the builder whose query to extend
     * @return Builder
     * the extended query builder
     */
    public function scopeUnrepaidLendings(Builder $query): Builder
    {
        return $query
            ->join('lendings', 'financial_operations.id','=','lendings.id')
            ->whereRaw('previous_lending_id is null')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->fromRaw('lendings as repay')
                    ->whereRaw('repay.previous_lending_id = financial_operations.id');
            });
    }

    /**
     * Generates the name of the directory where attachments for a user should be stored.
     *
     * @param User $user
     * @return string
     * the generated name
     */
    public static function getAttachmentsDirectoryPath(User $user)
    {
        return "attachments/user_{$user->id}";
    }

    /**
     * Returns the account to which this operation belongs.
     *
     * @return BelongsTo
     */
    public function account()
    {
        DB::enableQueryLog();
        return $this->accountUser->account;
    }

    /**
     * Returns the user to which this operation belongs.
     *
     * @return BelongsTo
     */
    public function user()
    {
        DB::enableQueryLog();
        return $this->accountUser->user;
    }

    /**
     * Returns the AccountUser to which this operation belongs.
     *
     * @return BelongsTo
     */
    public function accountUser()
    {
        DB::enableQueryLog();
        return $this->belongsTo(AccountUser::class, 'account_user_id');
    }

    /**
     * Returns the associated SAP operation, if it exists.
     *
     * @return BelongsTo
     */
    public function sapOperation()
    {
        return $this->belongsTo(SapOperation::class);
    }

    /**
     * Return true if the operation has an assiocated SAP operation
     * 
     * @return bool
     */
    public function isChecked()
    {
        return ! is_null($this->sapOperation);
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
     * Returns the lending record related to this operation, if it exists.
     *
     * @return HasOne
     */
    public function lending()
    {
        return $this->hasOne(Lending::class, 'id', 'id');
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
     * Returns whether this operation is a lending
     * (based on the operation type, not on the existence of a DB 'lending' record).
     *
     * @return bool
     */
    public function isLending()
    {
        return $this->operationType->lending;
    }

    /**
     * Returns whether this operation is a repayment - subtype of lending
     * (based on the operation type, not on the existence of a DB 'lending' record).
     *
     * @return bool
     */
    public function isRepayment()
    {
        return $this->operationType->repayment;
    }

    /**
     * Gets this operation's data in the order and format needed for a CSV export.
     *
     * @return array
     * an array filled with this operation's data
     */
    public function getExportData()
    {
        return [
            $this->account()->sap_id,
            $this->title,
            $this->date->format('d.m.Y'),
            $this->operationType->name,
            $this->subject,
            $this->getSumString(),
            $this->getCheckedString(),
            $this->sap_id
        ];
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

    /**
     * Generates a human-readable filename for this operation's attachment.
     *
     * @return string
     * the generated filename
     */
    public function generateAttachmentFileName()
    {
        $title = $this->getSanitizedTitle();
        $contentClause = trans('files.attachment');
        $fileName = "{$title}_$contentClause";

        return FileHelper::appendFileExtension($this->attachment, $fileName);
    }

}
