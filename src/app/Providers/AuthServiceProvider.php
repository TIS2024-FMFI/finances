<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\SapReport;
use App\Policies\AccountPolicy;
use App\Policies\FinancialOperationPolicy;
use App\Policies\SapReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Account::class => AccountPolicy::class,
        FinancialOperation::class => FinancialOperationPolicy::class,
        SapReport::class => SapReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
