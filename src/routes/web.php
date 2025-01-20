<?php

use App\Http\Controllers\FinancialAccounts\CreateAccountController;
use App\Http\Controllers\FinancialAccounts\DeleteAccountController;
use App\Http\Controllers\FinancialAccounts\UpdateAccountController;
use App\Http\Controllers\FinancialOperations\DeleteOperationController;
use App\Http\Controllers\UserAccountManagement\ManageUserAccountController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FinancialOperations\OperationsOverviewController;
use App\Http\Controllers\FinancialAccounts\AccountsOverviewController;
use App\Http\Controllers\FinancialOperations\CreateOperationController;
use App\Http\Controllers\FinancialOperations\UpdateOperationController;
use App\Http\Controllers\FinancialOperations\OperationDetailController;
use App\Http\Controllers\FinancialOperations\OperationCheckController;
use App\Http\Controllers\SapOperations\SapOperationCheckController;
use App\Http\Controllers\SapReports\ReportsOverviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelImportController;
use Tests\Feature\UserAccountManagement\ManageUserAccountTest;

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

    Route::get('/lendings-get/{lending}', [CreateOperationController::class, 'getLendingData']);

    Route::get('/lendings-get-opposite/{lending}', [CreateOperationController::class, 'getOpposite']);


    Route::middleware(['ajax', 'jsonify'])->group(function () {


        Route::get('/accounts/{account}/operations/create', [CreateOperationController::class, 'getFormData']);
        Route::post('/accounts/{account}/operations', [CreateOperationController::class, 'create'])
            ->middleware('can:create,App\Models\FinancialOperation,account');

        Route::middleware('can:update,operation')->group(function () {
            Route::get('/operations/{operation}/update', [UpdateOperationController::class, 'getFormData']);
            Route::patch('/operations/{operation}', [UpdateOperationController::class, 'update']);

            Route::patch('/operations/{operation}/status-accept', [UpdateOperationController::class, 'statusAccept']);
            Route::patch('/operations/{operation}/status-refuse', [UpdateOperationController::class, 'statusRefuse']);
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
    Route::middleware(['ajax', 'jsonify'])->group(function () {
        Route::post('/accounts/{account}/excel-upload', [ExcelImportController::class, 'upload'])->withoutMiddleware(['auth']);

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
     * User Account Management
     */
    Route::get('/accounts/{account}/add', [ManageUserAccountController::class, 'getFormData']);

    Route::middleware(['ajax', 'jsonify'])->group(function () {
        Route::post("/accounts/{account}/add", [ManageUserAccountController::class, 'addUserToAccount']);
        Route::delete('/accounts/{accountId}/users/{userId}', [ManageUserAccountController::class, 'removeUserFromAccount']);
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
