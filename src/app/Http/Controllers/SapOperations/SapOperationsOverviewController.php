<?php

namespace App\Http\Controllers\SapOperations;

use Illuminate\Http\Request;

class SapOperationsOverviewController extends Controller
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

            $operations = $account->userOperationsBetween($user, $dateFrom, $dateTo)->orderBy('date', 'desc')
                ->paginate($this::$resultsPerPage)->withQueryString();


        $incomes = $account->userOperationsBetween($user, $dateFrom, $dateTo)->incomes()->sum('sum');
        $expenses = $account->userOperationsBetween($user, $dateFrom, $dateTo)->expenses()->sum('sum');

        $accountBalance = $account->getBalance();
        // Upravený kód na získanie account_title
        $accountTitle = $account->users()->first()?->pivot?->account_title ?? 'Predvolený názov účtu';
        return view('finances.account', [
            'account' => $account,
            'account_title' => $accountTitle,
            'operations' => $operations,
            'incomes_total' => $incomes,
            'expenses_total' => $expenses,
            'account_balance' => $accountBalance,
            'users' => $users,
        ]);
    }

    public function admin_show(Account $account, DateRequest $request){

        $dateFrom = $request->getValidatedFromDateOrMin();
        $dateTo = $request->getValidatedToDateOrMax();
        $user = Auth::user();
        $users = null;
            $operations = $account->OperationsBetween( $dateFrom, $dateTo)->orderBy('date', 'desc')
                ->paginate($this::$resultsPerPage)->withQueryString();
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
        ]);

    }
}
