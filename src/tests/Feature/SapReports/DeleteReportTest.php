<?php

namespace Tests\Feature\SapReports;

use App\Models\Account;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteReportTest extends TestCase
{
    private $user, $account, $report;

    private $ajaxHeaders;

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

        $this->ajaxHeaders = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->setupDone = true;
    }

    public function tearDown(): void
    {
        if ($this->lastTest) {
            Storage::fake();
        }
    }

    public function test_that_unauthenticated_user_cannot_delete_report()
    {
        $response = $this->delete('/sap-reports/' . $this->report->id);

        $response
            ->assertStatus(302);
    }

    public function test_that_user_cannot_delete_nonexisting_report()
    {
        $response = $this->actingAs($this->user)
                            ->delete('/sap-reports/99999');
        
        $response
            ->assertStatus(404);
    }

    public function test_that_only_ajax_requests_are_handled()
    {
        $response = $this->actingAs($this->user)
                            ->delete('/sap-reports/' . $this->report->id);
        
        $response
            ->assertStatus(500);
    }

    public function test_that_user_can_delete_existing_report()
    {
        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->delete('/sap-reports/' . $this->report->id);
        
        $response
            ->assertStatus(200);

        $this->assertDatabaseMissing('sap_reports', [ 'id' => $this->report->id ]);
        Storage::assertMissing($this->report->path);

        $this->lastTest = true;
    }
}
