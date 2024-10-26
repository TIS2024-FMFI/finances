<?php

namespace Tests\Feature\FinancialOperations;

use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;
use Tests\Util\HiddenMembersAccessor;


/**
 * These tests must be run on a seeded database, as they generate plenty of models with foreign keys.
 */
class OperationsOverviewFilteringTest extends TestCase
{
    use DatabaseTransactions;

    private int $operationsPerPage;
    private array $dates;
    private $user;
    private $userWithPivot;
    private $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->operationsPerPage = HiddenMembersAccessor::getHiddenStaticProperty(
            '\App\Http\Controllers\FinancialOperations\OperationsOverviewController',
            'resultsPerPage'
        );
        for ($i = 0; $i < 6; $i++){
            $this->dates[$i] = Date::create(2000+$i);
        }
        $this->user = User::firstOrCreate([ 'email' => 'new@b.c' ]);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'title' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

    }

    public function test_operations_between()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[3]]);

        $this->assertCount(4, $this->account->operations);
        $this->assertCount(4, $this->account->operationsBetween($this->dates[0], $this->dates[3])->get());
        $this->assertCount(2, $this->account->operationsBetween($this->dates[0], $this->dates[1])->get());
        $this->assertCount(0, $this->account->operationsBetween($this->dates[4], $this->dates[5])->get());

    }

    public function test_invalid_operations_between()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $this->assertCount(2, $this->account->operations);
        $this->assertCount(0, $this->account->operationsBetween($this->dates[1], $this->dates[0])->get());

    }

    public function test_filtered_view_with_all_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[0]}&to={$this->dates[1]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');

        $this->assertCount(2,$response->viewData('operations'));
    }

    public function test_filtered_view_with_some_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[3]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[1]}&to={$this->dates[2]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');

        $this->assertCount(2,$response->viewData('operations'));
    }

    public function test_filtered_view_with_no_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[2]}&to={$this->dates[3]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');

        $this->assertCount(0,$response->viewData('operations'));
    }

    public function test_view_data_unbound_from_right()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[1]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');

        $this->assertCount(2,$response->viewData('operations'));
    }

    public function test_view_data_unbound_from_left()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?to={$this->dates[1]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');

        $this->assertCount(2,$response->viewData('operations'));
    }

    public function test_filtering_invalid_interval_causes_redirect()
    {
        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[1]}&to={$this->dates[0]}");

        $response->assertStatus(302);
    }

    public function test_filtering_invalid_input_causes_redirect()
    {

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from=invalid");

        $response->assertStatus(302);
    }

    public function test_pagination_is_used_with_filtered_data()
    {

        $count = $this->operationsPerPage;
        FinancialOperation::factory()->count($count)->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[0]}&to={$this->dates[1]}");

        $response->assertStatus(200)
            ->assertViewIs('finances.account');
        $operations = $response->viewData('operations');

        $this->assertCount($count,$operations);
        $this->assertTrue($operations->hasPages());
        $this->assertEquals($count+1,$operations->total());
        $this->assertEquals(2,$operations->lastPage());
    }

    public function test_second_page_with_filtered_data()
    {

        $count = $this->operationsPerPage;
        FinancialOperation::factory()->count($count)->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations?from={$this->dates[0]}&to={$this->dates[1]}");

        $url = $response->viewData('operations')->url(2);
        $response = $this->actingAs($this->user)->get($url);

        $response
            ->assertStatus(200)
            ->assertViewIs('finances.account');
        $operations = $response->viewData('operations');

        $this->assertCount(1,$operations);
        $this->assertFalse($operations->hasMorePages());
    }

}
