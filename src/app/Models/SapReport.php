<?php

namespace App\Models;

use App\Http\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'path',
        'exported_or_uploaded_on'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'exported_or_uploaded_on' => 'date',
    ];

    /**
     * Indicates if the model should be timestamped, using created_at and updated_at columns.
     *
     * @var mixed
     */
    public $timestamps = false;

    /**
     * Get the account with which the report is associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Generate a user-friendly name for the SAP report file represented by the model.
     *
     * @return string
     * the generated file name
     */
    public function generateReportFileName()
    {
        $sanitizedSapId = $this->account->getSanitizedSapId();
        $exportedOrUploadedOn = $this->exported_or_uploaded_on->format('d-m-Y');


        $isTxtFormat = \Illuminate\Support\Str::endsWith($this->path, '.txt');
        $contentClause = $isTxtFormat ? trans('files.sap_report') : "excel";

        $fileName = "${sanitizedSapId}_${contentClause}_${exportedOrUploadedOn}";

        return FileHelper::appendFileExtension($this->path, $fileName);
    }

    /**
     * Get the path to the directory within which a user's reports are stored.
     *
     * @param User $user
     * the user whose directory for reports to consider
     * @return string
     * the path to the user's directory
     */
    public static function getReportsDirectoryPath(User $user)
    {
        return 'reports/user_' . $user->id;
    }

    public static function getExcelReportsDirectoryPath(Account $account)
    {
        return 'reports/account_' . $account->id;
    }
}
