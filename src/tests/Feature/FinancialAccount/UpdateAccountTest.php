<?php

namespace Tests\Feature\FinancialAccount;

use App\Models\Account;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Log;

class UpdateAccountTest extends TestCase
{

    private $user, $userWithPivot, $account;

    private $ajaxHeaders;

    private $setupDone = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->setupDone) {
            return;
        }

        $this->user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'title' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

        $this->ajaxHeaders = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->setupDone = true;
    }

    public function test_that_unauthenticated_user_cannot_update_account()
    {
        $response = $this->put('/accounts/' . $this->account->id);

        $response
            ->assertStatus(302);
    }

    public function test_that_only_ajax_requests_are_handled()
    {
        $response = $this->actingAs($this->user)
                            ->put('/accounts/' . $this->account->id);

        $response
            ->assertStatus(500);
    }

    public function test_that_user_cannot_update_nonexisting_account()
    {
        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->put('/accounts/99999');

        $response
            ->assertStatus(404);
    }

    public function test_that_user_can_update_existing_account()
    {
        $updated =$this->account->getAttributes();
        $updated['title'] = $this->userWithPivot->pivot->account_title . ' new';
        $updated['sap_id'] .= '-00';
        unset($updated['id']);

        $response = $this->actingAs($this->user)
                            ->withHeaders($this->ajaxHeaders)
                            ->put(
                                '/accounts/' . $this->account->id,
                                $updated
                            );

        $response
            ->assertStatus(200);

        // the update controller detaches the old account if the sap_id is changed. Controller finds or creates
        // a new account and attaches it to the user
        $this->user->refresh();
        $newAttachedAccount = $this->user->accounts->where('sap_id', $updated['sap_id'])->first();

        /**Log::debug('NEW_ATTACHED_ACCOUNT {e}', ['e' => $this->user->accounts] );
        Log::debug('ACCOUNT_TITLE_REQUESTED {e}', ['e' => $updated['title']]);
        Log::debug('ACCOUNT_SAP_ID_REQUESTED {e}', ['e' => $updated['sap_id']]);

        Log::debug('ACCOUNT_TITLE {e}', ['e' => $newAttachedAccount->pivot->account_title]);
        Log::debug('ACCOUNT_SAP_ID {e}', ['e' => $newAttachedAccount->sap_id]);**/

        $this->assertEquals($updated['title'], $newAttachedAccount->pivot->account_title);
        $this->assertEquals($updated['sap_id'], $newAttachedAccount->sap_id);
    }

    public function test_that_user_can_update_account_title_only()
    {
        $updated =$this->account->getAttributes();
        $updated['title'] = $this->userWithPivot->pivot->account_title . ' new';
        unset($updated['id']);

        $response = $this->actingAs($this->user)
            ->withHeaders($this->ajaxHeaders)
            ->put(
                '/accounts/' . $this->account->id,
                $updated
            );

        $response
            ->assertStatus(200);

        $this->user->refresh();
        $newAttachedAccount = $this->user->accounts->where('sap_id', $updated['sap_id'])->first();

        $this->assertEquals($updated['title'], $newAttachedAccount->pivot->account_title);
        $this->assertEquals($updated['sap_id'], $newAttachedAccount->sap_id);
    }
}
