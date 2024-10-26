<?php

namespace Tests\Feature\FinancialAccount;

use App\Models\Account;
use App\Models\User;
use App\Models\FinancialOperation;
use App\Models\OperationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

use Illuminate\Support\Facades\Log;

class FinancialAccountBalanceTest extends TestCase
{
    use DatabaseTransactions;

    private Model $account, $userWithPivot, $incomeType, $expenseType;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $this->account = Account::factory()->create();
        $this->account->users()->attach($user, [ 'account_title' => 'title' ]);
        $this->userWithPivot = $this->account->users->where('id', $user->id)->first();
        
        $this->incomeType = OperationType::firstOrCreate(['name' => 'income', 'expense' => false, 'lending' => false]);
        $this->expenseType = OperationType::firstOrCreate(['name' => 'expense', 'expense' => true, 'lending' => false]);

    }

    public function test_zero_balance_with_no_operations()
    {
        $this->assertEquals(0,$this->account->getBalance());
    }

    public function test_positive_balance()
    {
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->incomeType,
            'sum' => 10]);

        $this->assertCount(1, $this->account->operations);
        $this->assertEquals(10, $this->account->getBalance());
    }

    public function test_negative_balance()
    {
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->expenseType,
            'sum' => 10]);

        $this->assertCount(1, $this->account->operations);
        $this->assertEquals(-10, $this->account->getBalance());
    }

    public function test_balance_with_multiple_operations()
    {
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->incomeType,
            'sum' => 10]);
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->expenseType,
            'sum' => 10]);

        $this->assertCount(2, $this->account->operations);
        $this->assertEquals(0, $this->account->getBalance());
    }

    public function test_balance_with_multiple_operations_2()
    {
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->incomeType,
            'sum' => 500]);
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->expenseType,
            'sum' => 250.50]);
        FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'operation_type_id' => $this->expenseType,
            'sum' => 385.95]);

        $this->assertCount(3, $this->account->operations);
        $this->assertEquals(-136.45, $this->account->getBalance());
    }

}
