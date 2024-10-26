<?php

namespace Tests\Feature\FinancialAccount;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CreateAccountTest extends TestCase
{
    use DatabaseTransactions;

    private $titleAttr;
    private $sapIdAttr;

    private $ajaxHeaders;

    public function setUp(): void
    {
        parent::setUp();

        $this->titleAttr = App::isLocale('en')
            ? 'title'
            : trans('validation.attributes.title');

        $this->sapIdAttr = trans('validation.attributes.sap_id');

        $this->ajaxHeaders = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];
    }

    public function test_request_failure_without_required_fields()
    {
        $user = User::firstOrCreate([ 'email' => 'new@b.c' ]);

        $response = $this->actingAs($user)
            ->withHeaders($this->ajaxHeaders)
            ->post(
                '/accounts',
                [
                    'title' => '',
                    'sap_id' => '',
                ]
            );

        $response->assertStatus(422);
        $this->assertDatabaseMissing('account_user', [
            'user_id' => $user->id,
            'account_title' => '',
        ]);
        $user->refresh();
        $this->assertCount(0, $user->accounts);
    }

    public function test_new_account_is_created()
    {
        $user = User::create([ 'email' => 'new@b.c' ]);

        $this->assertCount(0, $user->accounts);

        $response = $this->actingAs($user)
            ->withHeaders($this->ajaxHeaders)
            ->post(
                '/accounts',
                [
                    'title' => 'title',
                    'sap_id' => 'ID-123',
                ]
            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'sap_id' => 'ID-123'
        ]);

        $this->assertDatabaseHas('account_user', [
            'user_id' => $user->id,
            'account_title' => 'title',
        ]);

        $user->refresh();
        $this->assertCount(1, $user->accounts);
    }

    public function test_request_failure_when_title_too_long()
    {
        $user = User::create([ 'email' => 'new@b.c' ]);

        $response = $this->actingAs($user)
            ->withHeaders($this->ajaxHeaders)
            ->post(
                '/accounts',
                [
                    'title' => str_repeat('a', 256),
                    'sap_id' => '',
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.title.0',
                trans('validation.max.string', [ 'attribute' => $this->titleAttr, 'max' => 255 ])
            );
    }

    public function test_request_failure_when_sap_id_too_long()
    {
        $user = User::create([ 'email' => 'new@b.c' ]);

        $response = $this->actingAs($user)
            ->withHeaders($this->ajaxHeaders)
            ->post(
                '/accounts',
                [
                    'title' => '',
                    'sap_id' => str_repeat('a', 256),
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.sap_id.0',
                trans('validation.max.string', [ 'attribute' => $this->sapIdAttr, 'max' => 255 ])
            );
    }

    public function test_request_failure_when_sap_id_has_incorrect_format()
    {
        $user = User::create([ 'email' => 'new@b.c' ]);

        $response = $this->actingAs($user)
            ->withHeaders($this->ajaxHeaders)
            ->post(
                '/accounts',
                [
                    'title' => 'title',
                    'sap_id' => 'AO-06/0-',
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.sap_id.0',
                trans('validation.regex', [ 'attribute' => $this->sapIdAttr ])
            );
    }

    public function test_request_failure_when_sap_id_is_duplicate()
    {
        $user = User::create([ 'email' => 'new@b.c' ]);

        $this->assertCount(0, $user->accounts);

        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts',
                                [
                                    'title' => 'title',
                                    'sap_id' => 'ID-123',
                                ]
                            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('accounts', [
            'sap_id' => 'ID-123'
        ]);

        $this->assertDatabaseHas('account_user', [
            'user_id' => $user->id,
            'account_title' => 'title',
        ]);

        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/accounts',
                                [
                                    'title' => 'new title',
                                    'sap_id' => 'ID-123',
                                ]
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.sap_id.0',
                trans('validation.unique', [ 'attribute' => $this->sapIdAttr ])
            );

        $user->refresh();
        $this->assertCount(1, $user->accounts);
    }
}
