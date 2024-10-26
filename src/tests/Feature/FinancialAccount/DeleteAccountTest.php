<?php

namespace Tests\Feature\FinancialAccount;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    private $user, $account;

    private $ajaxHeaders;

    private $setupDone = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        $this->user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->create();

        $this->ajaxHeaders = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->setupDone = true;
    }

    public function test_that_unauthenticated_user_cannot_delete_account()
    {
        $response = $this->delete('/accounts/' . $this->account->id);

        $response
            ->assertStatus(302);
    }

    public function test_that_user_cannot_delete_nonexisting_account()
    {
        $response = $this->actingAs($this->user)
                            ->delete('/accounts/99999');
        
        $response
            ->assertStatus(404);
    }

    public function test_that_only_ajax_requests_are_handled()
    {
        $response = $this->actingAs($this->user)
                            ->delete('/accounts/' . $this->account->id);
        
        $response
            ->assertStatus(500);
    }

    public function test_that_user_can_delete_existing_account()
    {
        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->delete('/accounts/' . $this->account->id);
        
        $response
            ->assertStatus(200);

        $this->assertDatabaseMissing('accounts', [ 'id' => $this->account->id ]);
    }
}
