<?php

namespace App\Http\Controllers\SapReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Base\DateRequest;
use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * A controller responsible for presenting existing SAP reports to a user.
 *
 * This controller provides methods to:
 *      - show a list of SAP reports associated with an account
 */
class ReportsOverviewController extends Controller
{
    /**
     * The number of SAP reports to show on a single page.
     *
     * @var int
     */
    private static int $resultsPerPage = 15;


    /**
     * Show the SAP Reports view for an account with reports filtered based on
     * the date they were exported or uploaded. The filtered reports are paginated.
     *
     * This method displays a view containing a list of SAP reports associated with a specific account.
     * Reports are filtered by the date range specified in the request. Admin users can view all reports
     * within the date range, while non-admin users can only view reports associated with their accounts.
     *
     * @param \App\Http\Requests\Base\DateRequest $request The request containing the date interval used for filtering.
     * @param \App\Models\Account $account The account for which to show the SAP reports.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View The view that will be shown.
     */
    public function show(DateRequest $request, Account $account)
    {
        $from = $request->getValidatedFromDateOrMin();
        $to = $request->getValidatedToDateOrMax();

        // Admin users can view all reports within the specified date range.
        if (auth()->user()->is_admin) {
            $reports = SapReport::where('account_id', $account->id)
                ->whereBetween('exported_or_uploaded_on', [$from, $to])
                ->orderBy('exported_or_uploaded_on', 'desc')
                ->paginate(self::$resultsPerPage)
                ->withQueryString();
        } else {
            // Non-admin users can view only their reports for the specified account.
            $reports = $this->retrieveSapReports($account, $from, $to);
        }

        // Safely access the account title using the null-safe operator and provide a default value if not found.
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Default Account Title';

        return view('finances.sap_reports', [
            'account' => $account,
            'account_title' => $accountTitle,
            'reports' => $reports
        ]);
    }

    public function admin_show(DateRequest $request, Account $account)
    {
        $from = $request->getValidatedFromDateOrMin();
        $to = $request->getValidatedToDateOrMax();
        $user = null;
        // Admin users can view all reports within the specified date range.
        if (auth()->user()->is_admin) {
            $reports = SapReport::where('account_id', $account->id)
                ->whereBetween('exported_or_uploaded_on', [$from, $to])
                ->orderBy('exported_or_uploaded_on', 'desc')
                ->paginate(self::$resultsPerPage)
                ->withQueryString();
        } else {
            // Non-admin users can view only their reports for the specified account.
            $reports = $this->retrieveSapReports($account, $from, $to);
        }

        // Safely access the account title using the null-safe operator and provide a default value if not found.
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Default Account Title';

        return view('admin.sap_reports', [
            'account' => $account,
            'account_title' => $accountTitle,
            'reports' => $reports,
            'user' => $user
        ]);
    }

    public function admin_user_show(User $user, DateRequest $request, Account $account)
    {
        $from = $request->getValidatedFromDateOrMin();
        $to = $request->getValidatedToDateOrMax();

        // Admin users can view all reports within the specified date range.
        if (auth()->user()->is_admin) {
            $reports = SapReport::where('account_id', $account->id)
                ->whereBetween('exported_or_uploaded_on', [$from, $to])
                ->orderBy('exported_or_uploaded_on', 'desc')
                ->paginate(self::$resultsPerPage)
                ->withQueryString();
        } else {
            // Non-admin users can view only their reports for the specified account.
            $reports = $this->retrieveSapReports($account, $from, $to);
        }

        // Safely access the account title using the null-safe operator and provide a default value if not found.
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Default Account Title';

        return view('admin.user.sap_reports', [
            'account' => $account,
            'account_title' => $accountTitle,
            'reports' => $reports,
            'user' => $user
        ]);
    }


    /**
     * Retrieve the paginated SAP Reports for an account which were exported or
     * uploaded within a specified period.
     *
     * @param \App\Models\Account $account
     * the account for which to show the SAP reports
     * @param \Illuminate\Support\Carbon $from
     * the date determining the beginning of the period to consider (inclusive)
     * @param \Illuminate\Support\Carbon $to
     * the date determining the end of the period to consider (inclusive)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * the paginated and filtered reports
     */
    private function retrieveSapReports(Account $account, Carbon $from, Carbon $to)
    {
        return $account
            ->sapReportsBetween($from, $to)
            ->orderBy('exported_or_uploaded_on', 'desc')
            ->paginate($this::$resultsPerPage)
            ->withQueryString();
    }
}
