<?php

namespace Tests\Feature\FinancialOperations;

use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Util\HiddenMembersAccessor;

/**
 * These tests must be run on a seeded database, as they generate plenty of models with foreign keys.
 */
class OperationsOverviewTest extends TestCase
{
    use DatabaseTransactions;

    private int $operationsPerPage;
    private Model $user, $userWithPivot, $account, $type, $lendingType;
    private array $headers;

    public function setUp(): void
    {
        parent::setUp();

        $this->operationsPerPage = HiddenMembersAccessor::getHiddenStaticProperty(
            '\App\Http\Controllers\FinancialOperations\OperationsOverviewController',
            'resultsPerPage'
        );

        $this->user = User::firstOrCreate([ 'email' => 'new@b.c' ]);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

        $this->type = OperationType::firstOrCreate(['name' => 'type']);
        $this->lendingType = OperationType::firstOrCreate(['name' => 'lending', 'lending' => true]);

        $this->headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];
    }

    public function test_correct_view()
    {
        $account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account1' ])->create();
        $response = $this->actingAs($this->user)->get("/accounts/$account->id/operations");
        $response
            ->assertStatus(200)
            ->assertViewIs('finances.account');
    }

    public function test_correct_view_data()
    {
        $account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account2' ])->create();
        $userWithPivot = $account->users->where('id', $this->user->id)->first();
        for ($i=0; $i<5; $i++)
        {
            $operation = FinancialOperation::factory()->create([
                'account_user_id' => $userWithPivot->pivot->id,
                'operation_type_id' => $this->type
            ]);
        }

        $response = $this->actingAs($this->user)->get("/accounts/$account->id/operations");
        $response
            ->assertStatus(200)
            ->assertViewIs('finances.account');

        $data = $response->viewData('operations');

        $this->assertCount(5,$data);
    }

    public function test_pagination_is_used()
    {

        $count = $this->operationsPerPage;
        $account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account3' ])->create();
        $userWithPivot = $account->users->where('id', $this->user->id)->first();
        for ($i=0; $i<$count+1; $i++)
        {
            $operation = FinancialOperation::factory()->create([
                'account_user_id' => $userWithPivot->pivot->id,
                'operation_type_id' => $this->type
            ]);
        }

        $response = $this->actingAs($this->user)->get("/accounts/$account->id/operations");
        $response
            ->assertStatus(200)
            ->assertViewIs('finances.account');

        $operations = $response->viewData('operations');

        $this->assertCount($count,$operations);

        $this->assertTrue($operations->hasPages());
        $this->assertEquals($count+1,$operations->total());
        $this->assertEquals(2,$operations->lastPage());

    }

    public function test_paging_second_page()
    {

        $count = $this->operationsPerPage;
        $account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account3' ])->create();
        $userWithPivot = $account->users->where('id', $this->user->id)->first();
        for ($i=0; $i<$count+1; $i++)
        {
            $operation = FinancialOperation::factory()->create([
                'account_user_id' => $userWithPivot->pivot->id,
                'operation_type_id' => $this->type
            ]);
        }

        $response = $this->actingAs($this->user)->get("/accounts/$account->id/operations?page=2");
        $response
            ->assertStatus(200)
            ->assertViewIs('finances.account');

        $operations = $response->viewData('operations');

        $this->assertCount(1,$operations);
        $this->assertFalse($operations->hasMorePages());
    }

}
