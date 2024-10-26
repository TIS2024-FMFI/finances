<?php

namespace Tests\Feature\SapReports;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadReportTest extends TestCase
{
    private $user, $account;
    private $sapReportAttr;

    private $ajaxHeaders;

    private $setupDone = false, $lastTest = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        Storage::fake();

        $this->sapReportAttr = trans('validation.attributes.sap_report');

        $this->user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->for($this->user)->create();

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

    public function test_that_unauthenticated_user_cannot_upload_report()
    {
        $response = $this->post(
                            '/accounts/' . $this->account->id . '/sap-reports',
                            [ 'sap_report' => '' ]
                        );

        $response
            ->assertStatus(302);
    }

    public function test_that_only_ajax_requests_are_handled()
    {
        $response = $this->actingAs($this->user)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                [ 'sap_report' => '' ]
                            );

        $response
            ->assertStatus(500);
    }

    public function test_that_user_cannot_associate_report_with_nonexisting_account()
    {
        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/99999/sap-reports',
                                [ 'sap_report' => '' ]
                            );

        $response
            ->assertStatus(404);
    }

    public function test_that_sap_report_is_required()
    {
        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                [ 'sap_report' => '' ]
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.sap_report.0',
                trans('validation.required', [ 'attribute' => $this->sapReportAttr ])
            );
    }

    public function test_that_sap_report_must_be_text_file()
    {
        $uploadedReport = UploadedFile::fake()
                            ->create('test', 0, 'application/pdf');

        $requestData = [
            'sap_report' => $uploadedReport,
        ];

        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                $requestData
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.sap_report.0',
                trans('validation.mimes', [ 'attribute' => $this->sapReportAttr, 'values' => 'txt'])
            );
    }

    public function test_that_sap_report_is_uploaded_with_date_uploaded()
    {
        $uploadedReport = UploadedFile::fake()
                            ->create('test', 0, 'text/plain');

        $requestData = [
            'sap_report' => $uploadedReport,
        ];

        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                $requestData
                            );

        $response
            ->assertStatus(201);

        $report = $this->account->sapReports()->first();
        $this->assertNotNull($report);
        $this->assertEquals(
            now()->format('d-m-Y'),
            $report->exported_or_uploaded_on->format('d-m-Y')
        );

        Storage::assertExists($report->path);

        $report->delete();
    }

    public function test_that_sap_report_is_uploaded_with_date_exported()
    {
        $exported = '4.5.2020';
        $reportContent = $exported . ' test';

        $uploadedReport = UploadedFile::fake()
                            ->createWithContent('test', $reportContent)
                            ->mimeType('text/plain');

        $requestData = [
            'sap_report' => $uploadedReport,
        ];

        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts/' . $this->account->id . '/sap-reports',
                                $requestData
                            );

        $response
            ->assertStatus(201);

        $report = $this->account->sapReports()->first();
        $this->assertNotNull($report);
        $this->assertEquals(
            Carbon::createFromFormat('d.m.Y', $exported)->format('d-m-Y'),
            $report->exported_or_uploaded_on->format('d-m-Y')
        );

        Storage::assertExists($report->path);

        $report->delete();

        $this->lastTest = true;
    }
}
