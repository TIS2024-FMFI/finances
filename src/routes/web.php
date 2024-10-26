<?php

use App\Http\Controllers\FinancialAccounts\CreateAccountController;
use App\Http\Controllers\FinancialAccounts\DeleteAccountController;
use App\Http\Controllers\FinancialAccounts\UpdateAccountController;
use App\Http\Controllers\FinancialOperations\DeleteOperationController;
use App\Http\Controllers\UserAccountManagement\ManageUserAccountController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FinancialOperations\OperationsOverviewController;
use App\Http\Controllers\FinancialAccounts\AccountsOverviewController;
use App\Http\Controllers\FinancialOperations\CreateOperationController;
use App\Http\Controllers\FinancialOperations\UpdateOperationController;
use App\Http\Controllers\FinancialOperations\OperationDetailController;
use App\Http\Controllers\FinancialOperations\OperationCheckController;
use App\Http\Controllers\SapOperations\SapOperationCheckController;
use App\Http\Controllers\SapReports\DeleteReportController;
use App\Http\Controllers\SapReports\ReportDetailController;
use App\Http\Controllers\SapReports\ReportsOverviewController;
use App\Http\Controllers\SapReports\UploadReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Authentication
 */

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/login/{token}', [LoginController::class, 'loginUsingToken'])
        ->name('login-using-token');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])
        ->name('forgot-password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLoginLink'])
        ->middleware(['ajax', 'jsonify']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::post('/register', [RegisterController::class, 'register'])
    ->middleware(['ajax', 'jsonify']);


/**
 * User Account Management
 */

Route::post('/change-password', [ManageUserAccountController::class, 'changePassword'])
    ->middleware(['auth', 'auth.session', 'ajax', 'jsonify']);


/**
 * Finances
 */

Route::middleware(['auth', 'auth.session'])->group(function () {
    /**
     * Financial Accounts
     */

    Route::permanentRedirect('/', 'accounts');
    Route::get('/accounts', [AccountsOverviewController::class, 'show'])
        ->name('home');

    Route::middleware(['ajax', 'jsonify'])->group(function () {
        Route::post('/accounts', [CreateAccountController::class, 'create']);

        Route::put('/accounts/{account}', [UpdateAccountController::class, 'update'])
            ->middleware('can:update,account');

        Route::delete('/accounts/{account}', [DeleteAccountController::class, 'delete'])
            ->middleware('can:delete,account');
    });


    /**
     * Financial Operations
     */

    Route::middleware('can:view,account')->group(function () {
        Route::get('/accounts/{account}/operations', [OperationsOverviewController::class, 'show']);
        Route::get('/accounts/{account}/operations/export', [OperationsOverviewController::class, 'downloadExport']);
    });


    Route::get('/operations/{operation}/attachment', [OperationDetailController::class, 'downloadAttachment']);

    Route::get('/operations/{operation}', [OperationDetailController::class, 'getData']);
    Route::get('/operations/{operation}/check', [OperationCheckController::class, 'getFormData']);
    Route::get('/operations/{operation}/uncheck', [OperationCheckController::class, 'getUncheckData']);


    Route::middleware(['ajax', 'jsonify'])->group(function () {
        Route::get('/accounts/{account}/operations/create', [CreateOperationController::class, 'getFormData']);
        Route::post('/accounts/{account}/operations', [CreateOperationController::class, 'create'])
            ->middleware('can:create,App\Models\FinancialOperation,account');

        Route::middleware('can:update,operation')->group(function () {
            Route::get('/operations/{operation}/update', [UpdateOperationController::class, 'getFormData']);
            Route::patch('/operations/{operation}', [UpdateOperationController::class, 'update']);
        });

        Route::post('/operations/{lending}/repayment', [CreateOperationController::class, 'createRepayment'])
            ->middleware('can:createRepayment,App\Models\FinancialOperation,lending');

        Route::delete('/operations/{operation}', [DeleteOperationController::class, 'delete'])
            ->middleware('can:delete,operation');

        Route::post("/operations/{operation}/check", [OperationCheckController::class, 'checkOperation']);
        Route::delete("/operations/{operation}/uncheck", [OperationCheckController::class, 'uncheckOperation']);
    });


    /**
     * SAP Reports
     */

    Route::get('/accounts/{account}/sap-reports', [ReportsOverviewController::class, 'show'])
        ->middleware('can:view,account');

    Route::get('/sap-reports/{report}/raw', [ReportDetailController::class, 'download'])
        ->middleware('can:view,report')
        ->name('sap-report-raw');

    Route::middleware(['ajax', 'jsonify'])->group(function () {
        Route::post('/accounts/{account}/sap-reports', [UploadReportController::class, 'upload'])
            ->middleware('can:create,App\Models\SapReport,account');

        Route::delete('/sap-reports/{report}', [DeleteReportController::class, 'delete'])
            ->middleware('can:delete,report');

        Route::post('/accounts/{account}/excel-upload', [ExcelImportController::class, 'upload']);

    });
    /**
     * SAP Operations
     */

    Route::get('/sapOperations/{operation}/check', [SapOperationCheckController::class, 'getFormData']);
    Route::get('/sapOperations/{operation}/uncheck', [SapOperationCheckController::class, 'getUncheckData']);

    Route::middleware(['ajax', 'jsonify'])->group(function () {

        Route::post("/sapOperations/{operation}/check", [SapOperationCheckController::class, 'checkOperation']);
        Route::delete("/sapOperations/{operation}/uncheck", [SapOperationCheckController::class, 'uncheckOperation']);

    });

    /**
     * Admin
     */

    Route::get('/user/{user}/accounts', [AccountsOverviewController::class, 'admin_user_show']);
    Route::get('/overview', [AccountsOverviewController::class, 'admin_show'])->name('admin_home');

    Route::get('/user/{user}/accounts/{account}/sap-reports', [ReportsOverviewController::class, 'admin_user_show']);
    Route::get('/overview/accounts/{account}/sap-reports', [ReportsOverviewController::class, 'admin_show']);


    Route::get('/user/{user}/accounts/{account}/operations', [OperationsOverviewController::class, 'admin_user_show']);
    Route::get('/overview/accounts/{account}/operations', [OperationsOverviewController::class, 'admin_show']);
    Route::middleware(['ajax', 'jsonify'])->group(function () {

        Route::post('/user/{user}/accounts/{account}/operations', [CreateOperationController::class, 'createAdmin']);
        Route::post('/user/{user}/accounts/', [CreateAccountController::class, 'createAdmin']);
        Route::post('/user/{user}/operations/{lending}/repayment', [CreateOperationController::class, 'createRepaymentAdmin']);
    });


});
