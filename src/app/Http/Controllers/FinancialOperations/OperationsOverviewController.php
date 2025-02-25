<?php

namespace App\Http\Controllers\FinancialOperations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\DateRequest;
use App\Models\Account;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Manages the 'operations overview' view, as well as listing, filtering and exporting operations.
 */
class OperationsOverviewController extends Controller
{
    /**
     * @var int
     * number of operations to be shown on one page
     */
    private static int $resultsPerPage = 15;

    /**
     * Fills the 'operations overview' view with financial operations belonging to a financial account.
     * The operations are paginated and can be filtered by date.
     *
     * @param Account $account
     * the financial account to which the operations belong
     * @param DateRequest $request
     * a HTTP request which may contain the dates to filter the operations by
     * @return Application|Factory|View
     * the view filled with data
     */
    public function show(Account $account, DateRequest $request)
    {

        $dateFrom = $request->getValidatedFromDateOrMin();
        $dateTo = $request->getValidatedToDateOrMax();
        $user = Auth::user();
        $users = null;
        $search = $request->input('search', null);
        $status = $request->input('status', null);
        $operationType = $request->input('operation_type', null);

        $isAccountAdmin = false;

        if ($user->id == $account->grantee){
            $users = $account->users;
            $isAccountAdmin = true;
        }

        $query = $account->userOperationsBetween($user, $dateFrom, $dateTo, $isAccountAdmin)->orderBy('date', 'desc');
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        if ($operationType) {
            $operationTypes = explode(',', $operationType);
            $query->whereIn('operation_type_id', $operationTypes);
        }
        $operations = $query->paginate($this::$resultsPerPage)->withQueryString();

        // $operations = $account->userOperationsBetween($user, $dateFrom, $dateTo)->orderBy('date', 'desc')
        // ->paginate($this::$resultsPerPage)->withQueryString();
        $sapOperations = $account->sapOperations;

        $total_incomes = 0;
        $total_expenses = 0;
        $my_total_incomes = 0;
        $my_total_expenses = 0;

        if ($isAccountAdmin){
            $total_incomes = $account->userOperationsBetween($user, $dateFrom, $dateTo, $isAccountAdmin)->incomes()->sum('sum');
            $total_expenses = $account->userOperationsBetween($user, $dateFrom, $dateTo, $isAccountAdmin)->expenses()->sum('sum');
        }

        $my_total_incomes = $account->userOperationsBetween($user, $dateFrom, $dateTo)->incomes()->sum('sum');
        $my_total_expenses = $account->userOperationsBetween($user, $dateFrom, $dateTo)->expenses()->sum('sum');

        // Upravený kód na získanie account_title
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Predvolený názov účtu';
        return view('finances.account', [
            'account' => $account,
            'account_title' => $accountTitle,
            'operations' => $operations,
            'sapOperations' => $sapOperations,
            'incomes_total' => $total_incomes,
            'expenses_total' => $total_expenses,
            'my_incomes_total' => $my_total_incomes,
            'my_expenses_total' => $my_total_expenses,
            'users' => $users,
            'status' => $status,
            'operation_type' => $operationType,
            'search' => $search,
            'isAccountAdmin' => $isAccountAdmin,
        ]);
    }

    public function admin_show(Account $account, DateRequest $request){

        $dateFrom = $request->getValidatedFromDateOrMin();
        $dateTo = $request->getValidatedToDateOrMax();
        $user = Auth::user();
        $users = null;
        $search = $request->input('search', null);
        $status = $request->input('status', null);
        $operationType = $request->input('operation_type', null);

        $query = $account->userOperationsBetween($user, $dateFrom, $dateTo)->orderBy('date', 'desc');
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        if ($operationType) {
            $operationTypes = explode(',', $operationType);
            $query->whereIn('operation_type_id', $operationTypes);
        }
        $operations = $query->paginate($this::$resultsPerPage)->withQueryString();

        $sapOperations = $account->sapOperations;

        // $operations = $account->OperationsBetween( $dateFrom, $dateTo)->orderBy('date', 'desc')
        // ->paginate($this::$resultsPerPage)->withQueryString();
        $users = $account->users;
        $incomes = $account->userOperationsBetween($user, $dateFrom, $dateTo)->incomes()->sum('sum');
        $expenses = $account->userOperationsBetween($user, $dateFrom, $dateTo)->expenses()->sum('sum');
        $accountBalance = $account->getBalance();
        // Upravený kód na získanie account_title
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Predvolený názov účtu';
        return view('admin.account', [
            'account' => $account,
            'account_title' => $accountTitle,
            'operations' => $operations,
            'incomes_total' => $incomes,
            'expenses_total' => $expenses,
            'account_balance' => $accountBalance,
            'users' => $users,
            'sapOperations' => $sapOperations,
            'status' => $status,
            'operation_type' => $operationType,
            'search' => $search,
        ]);

    }

    public function admin_user_show(User $user,Account $account,DateRequest $request)
    {
        $dateFrom = $request->getValidatedFromDateOrMin();
        $dateTo = $request->getValidatedToDateOrMax();
        $sapOperations = $account->sapOperations;

        // $operations = $account->userOperationsBetween($user, $dateFrom, $dateTo)->orderBy('date', 'desc')
            // ->paginate($this::$resultsPerPage)->withQueryString();

        $search = $request->input('search', null);
        $status = $request->input('status', null);
        $operationType = $request->input('operation_type', null);

        $query = $account->userOperationsBetween($user, $dateFrom, $dateTo)->orderBy('date', 'desc');
        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        if ($operationType) {
            $operationTypes = explode(',', $operationType);
            $query->whereIn('operation_type_id', $operationTypes);
        }
        $operations = $query->paginate($this::$resultsPerPage)->withQueryString();

        $incomes = $account->userOperationsBetween($user, $dateFrom, $dateTo)->incomes()->sum('sum');
        $expenses = $account->userOperationsBetween($user, $dateFrom, $dateTo)->expenses()->sum('sum');

        $accountBalance = $account->getBalance();
        // Upravený kód na získanie account_title
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Predvolený názov účtu';
        return view('admin.user.account', [
            'account' => $account,
            'account_title' => $accountTitle,
            'operations' => $operations,
            'incomes_total' => $incomes,
            'expenses_total' => $expenses,
            'account_balance' => $accountBalance,
            'user' => $user,
            'sapOperations' => $sapOperations,
            'status' => $status,
            'operation_type' => $operationType,
            'search' => $search,
        ]);
    }


        /**
     * Handles a request to download a CSV export of financial operations.
     *
     * @param Account $account
     * the financial account to which the operations belong
     * @param DateRequest $request
     * a HTTP request which may contain the dates to filter the operations by
     * @return StreamedResponse
     * a response allowing the user to download the exported CSV file
     */
    public function downloadExport(Account $account, DateRequest $request)
    {
        $dateFrom = $request->getValidatedFromDateOrMin();
        $dateTo = $request->getValidatedToDateOrMax();
        $search = $request->input('search', null);
        $status = $request->input('status', null);
        $operationType = $request->input('operation_type', null);

        // $operations = $account->operationsBetween($dateFrom, $dateTo)->orderBy('date', 'desc')->get();

        $query = $account->operationsBetween($dateFrom, $dateTo)->orderBy('date', 'desc');

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        if ($operationType) {
            $operationTypes = explode(',', $operationType);
            $query->whereIn('operation_type_id', $operationTypes);
        }
        if ($status !== null) {
            $query->where('status', $status);
        }
        $operations = $query->get();
        $filename = $this->generateExportName($account, $dateFrom, $dateTo, $search, $operationType, $status);

        return response()->streamDownload(
            fn () => $this->generateCsvFile($operations),
            $filename,
            ['content-type' => 'text/csv']
        );
    }

    /**
     * Generates a name for the CSV export file, containing the name of the account.
     * If the dates in the export are limited, the bounding dates are present in the name as well.
     *
     * @param $account
     * the financial account to which the operations belong
     * @param $dateFrom
     * first day in the filtered interval
     * @param $dateTo
     * last day in the filtered interval
     * @return string
     * the generated file name
     */
    private function generateExportName(Account $account, Carbon $dateFrom, Carbon $dateTo, $search, $operationType, $status)
    {
        $sap_id  = $account->getSanitizedSapId();
        $from = $this->generateFromString($dateFrom);
        $to = $this->generateToString($dateTo);
        $filters = '';
        if ($search) {
            $filters .= "_search_{$search}";
        }
        if ($operationType) {
            $filters .= "_type_{$operationType}";
        }
        if ($status) {
            $filters .= "_status_{$status}";
        }

        return "{$sap_id}_export{$from}{$to}{$filters}.csv";
        // return "{$sap_id}_export{$from}{$to}.csv";
    }

    /**
     * If the filtering interval is bound by the earliest date, generates a string describing that date.
     * Otherwise, generates an empty string.
     *
     * @param Carbon $dateFrom
     * the earliest date of the interval
     * @return string
     * the generated string
     */
    private function generateFromString(Carbon $dateFrom)
    {
        $from = '';

        if ($dateFrom != Date::minValue()) {
            $fromClause = trans('files.from');
            $from = "_{$fromClause}_{$this->formatDate($dateFrom)}";
        }

        return $from;
    }

    /**
     * If the filtering interval is bound by the latest date, generates a string describing that date.
     * Otherwise, generates an empty string.
     *
     * @param Carbon $dateTo
     * the latest date of the interval
     * @return string
     * the generated string
     */
    private function generateToString(Carbon $dateTo)
    {
        $to = '';

        if ($dateTo != Date::maxValue()) {
            $toClause = trans('files.to');
            $to = "_{$toClause}_{$this->formatDate($dateTo)}";
        }

        return $to;
    }

    /**
     * Creates a string in the 'd-m-Y' format from a date object.
     *
     * @param Carbon $date
     * the date to be formatted
     * @return string
     * the formatted string
     */
    private function formatDate(Carbon $date)
    {
        return Date::parse($date)->format('d-m-Y');
    }

    /**
     * Writes a CSV file into output file stream, containing data about financial operations.
     *
     * @param Collection $operations
     * collection of financial operations
     * @return false|resource
     * stream containing the exported file
     */
    private function generateCsvFile(Collection $operations)
    {
        $columns = [
            'SAP ID účtu', 'Názov', 'Dátum', 'Typ', 'Subjekt', 'Suma', 'Skontrolované', 'SAP ID operácie'
        ];
        $stream = fopen('php://output', 'w');
        fputcsv($stream,$columns,';');

        foreach ($operations as $operation)
            fputcsv($stream,$operation->getExportData(),';');

        fclose($stream);
        return $stream;
    }
}
