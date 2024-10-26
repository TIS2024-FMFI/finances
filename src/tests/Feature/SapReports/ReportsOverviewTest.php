<?php

namespace Tests\Feature\SapReports;

use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Util\HiddenMembersAccessor;

class ReportsOverviewTest extends TestCase
{
    private $reportsPerPage, $pages, $pageOverflow;
    private $user, $account, $reports;

    private $setupDone = false, $lastTest = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        Storage::fake();

        $this->reportsPerPage = HiddenMembersAccessor::getHiddenStaticProperty(
            '\App\Http\Controllers\SapReports\ReportsOverviewController',
            'resultsPerPage'
        );
        $this->pages = 2;
        $this->pageOverflow = 1;

        $this->user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->for($this->user)->create();
        $this->reports = SapReport::factory()
                            ->count($this->reportsPerPage * ($this->pages - 1) + $this->pageOverflow)
                            ->for($this->account)
                            ->create();

        $this->setupDone = true;
    }

    public function tearDown(): void
    {
        if ($this->lastTest) {
            Storage::fake();
        }
    }

    public function test_that_unauthenticated_user_cannot_view_reports()
    {
        $response = $this->get('/accounts/' . $this->account->id . '/sap-reports');

        $response
            ->assertStatus(302);
    }

    public function test_that_user_cannot_view_reports_for_nonexisting_account()
    {
        $response = $this->actingAs($this->user)
                            ->get('/accounts/99999/sap-reports');
        
        $response
            ->assertStatus(404);
    }

    public function test_that_correct_view_is_shown()
    {
        $response = $this->actingAs($this->user)
                            ->get('/accounts/' . $this->account->id . '/sap-reports');

        $response
            ->assertStatus(200)
            ->assertViewIs('finances.sap_reports');
    }

    public function test_that_results_are_paginated()
    {
        $response = $this->actingAs($this->user)
                            ->get('/accounts/' . $this->account->id . '/sap-reports');
        
        $response
            ->assertStatus(200)
            ->assertViewIs('finances.sap_reports');

        $reports = $response->viewData('reports');

        $this->assertCount($this->reportsPerPage, $reports);
        $this->assertTrue($reports->hasPages());
        $this->assertEquals($this->reports->count(), $reports->total());
        $this->assertEquals($this->pages, $reports->lastPage());
    }

    public function test_that_last_page_is_available()
    {
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?page=' . $this->pages
                            );

        $response
            ->assertStatus(200)
            ->assertViewIs('finances.sap_reports');
        
        $reports = $response->viewData('reports');

        $this->assertCount($this->pageOverflow, $reports);
        $this->assertFalse($reports->hasMorePages());
    }

    public function test_that_from_must_be_date()
    {
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?from=test'
                            );

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors('from');
    }

    public function test_that_from_is_used()
    {
        $sorted = $this->reports->sortBy('exported_or_uploaded_on');
        
        $filtered = $sorted->skip($this->reports->count() / 2);
        
        $expectedTotal = $filtered->count();
        $from = $filtered->first()['exported_or_uploaded_on'];
        
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?from=' . $from
                            );

        $response
            ->assertStatus(200);

        $reports = $response->viewData('reports');

        $this->assertEquals($expectedTotal, $reports->total());
    }

    public function test_that_to_must_be_date()
    {
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?to=test'
                            );

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors('to');
    }

    public function test_that_to_is_used()
    {
        $sorted = $this->reports->sortByDesc('exported_or_uploaded_on');
        
        $filtered = $sorted->skip($this->reports->count() / 2);
        
        $expectedTotal = $filtered->count();
        $to = $filtered->first()['exported_or_uploaded_on'];
        
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?to=' . $to
                            );

        $response
            ->assertStatus(200);

        $reports = $response->viewData('reports');
        
        $this->assertEquals($expectedTotal, $reports->total());
    }

    public function test_that_to_must_be_after_or_equal_to_from()
    {
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports?from=2000-01-01&to=1999-01-01'
                            );

        $response
            ->assertStatus(302)
            ->assertSessionHasErrors('to');
    }

    public function test_that_from_and_to_is_used()
    {
        $sorted = $this->reports->sortBy('exported_or_uploaded_on');
        
        $filtered = $sorted->skip($this->reports->count() / 4);
        $filtered = $filtered->take($filtered->count() / 2);
        
        $expectedTotal = $filtered->count();
        $from = $filtered->first()['exported_or_uploaded_on'];
        $to = $filtered->last()['exported_or_uploaded_on'];
        
        $response = $this->actingAs($this->user)
                            ->get(
                                '/accounts/'. $this->account->id
                                . '/sap-reports'
                                ."?from=$from&to=$to"
                            );

        $response
            ->assertStatus(200);

        $reports = $response->viewData('reports');
        
        $this->assertEquals($expectedTotal, $reports->total());

        $this->lastTest = true;
    }
}
