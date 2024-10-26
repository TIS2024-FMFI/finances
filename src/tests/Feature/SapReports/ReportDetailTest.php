<?php

namespace Tests\Feature\SapReports;

use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportDetailTest extends TestCase
{
    private $user, $account, $report;

    private $setupDone = false, $lastTest = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        Storage::fake();

        $this->user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->for($this->user)->create();
        $this->report = SapReport::factory()->for($this->account)->create();

        $this->setupDone = true;
    }

    public function tearDown(): void
    {
        if ($this->lastTest) {
            Storage::fake();
        }
    }

    public function test_that_unauthenticated_user_cannot_download_report()
    {
        $response = $this->get('/sap-reports/' . $this->report->id . '/raw');

        $response
            ->assertStatus(302);
    }

    public function test_that_user_cannot_download_nonexisting_report()
    {
        $response = $this->actingAs($this->user)
                            ->get('/sap-reports/99999/raw');
        
        $response
            ->assertStatus(404);
    }

    public function test_that_user_can_download_existing_report()
    {
        $response = $this->actingAs($this->user)
                            ->get('/sap-reports/' . $this->report->id . '/raw');

        $expectedName =
            $this->account->getSanitizedSapId()
            . '_' . trans('files.sap_report') . '_'
            . Date::parse($this->report->exported_or_uploaded_on)->format('d-m-Y')
            . '.txt';
        
        $response
            ->assertStatus(200)
            ->assertDownload($expectedName);

        $this->lastTest = true;
    }
}
