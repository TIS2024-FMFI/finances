<?php

namespace Tests\Feature\Auth\Policies;

use App\Http\Controllers\FinancialOperations\GeneralOperationController;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\OperationType;
use App\Models\SapReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SapReportPolicyTest extends TestCase
{
    private $otherUser;
    private $account, $report;

    private $ajaxHeaders;

    private $setupDone = false, $lastTest = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        Storage::fake();

        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->hasAttached($user, [ 'account_title' => 'account' ])->create();
        $this->report = SapReport::factory()->for($this->account)->create();
        
        $this->otherUser = User::firstOrCreate([ 'email' => 'new@b.c' ]);

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

    public function test_that_unauthorized_user_cannot_download_report()
    {
        $response = $this->actingAs($this->otherUser)
                            ->get('/sap-reports/' . $this->report->id . '/raw');
        
        $response
            ->assertStatus(403);
    }

    public function test_that_unauthorized_user_cannot_upload_report()
    {
        $uploadedReport = UploadedFile::fake()
                            ->create('test', 0, 'text/plain');

        $requestData = [
            'sap_report' => $uploadedReport,
        ];

        $response = $this->actingAs($this->otherUser)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                $requestData
                            );
        
        $response
            ->assertStatus(403);
    }

    public function test_that_unauthorized_user_cannot_delete_report()
    {
        $response = $this->actingAs($this->otherUser)
                            ->withHeaders($this->ajaxHeaders)
                            ->delete('/sap-reports/' . $this->report->id);
        
        $response
            ->assertStatus(403);

        $this->lastTest = true;
    }
}
