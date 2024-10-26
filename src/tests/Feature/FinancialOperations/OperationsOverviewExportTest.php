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
class OperationsOverviewExportTest extends TestCase
{
    use DatabaseTransactions;

    private int $operationsPerPage, $extraRows;
    private array $dates;
    private Model $user, $userWithPivot, $account, $incomeType, $expenseType, $lendingType;
    private string $fromClause, $toClause;

    public function setUp(): void
    {
        parent::setUp();

        $this->operationsPerPage = HiddenMembersAccessor::getHiddenStaticProperty(
            '\App\Http\Controllers\FinancialOperations\OperationsOverviewController',
            'resultsPerPage'
        );
        $this->extraRows = 2; // header + extra '\n' symbol in a csv file
        $this->dates = ['2000-01-01', '2001-01-01', '2002-01-01', '2003-01-01', '2004-01-01','2005-01-01'];

        $this->user = User::firstOrCreate([ 'email' => 'new@b.c' ]);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

        $this->incomeType = OperationType::firstOrCreate(['name' => 'income', 'expense' => false, 'lending' => false]);
        $this->expenseType = OperationType::firstOrCreate(['name' => 'expense', 'expense' => true, 'lending' => false]);
        $this->lendingType = OperationType::firstOrCreate(['name' => 'lending', 'expense' => false, 'lending' => true]);

        $this->fromClause = trans('files.from');
        $this->toClause = trans('files.to');

    }

    public function test_export_single_operation()
    {
        $operation = FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->incomeType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test'
        ]);

        $response = $this->actingAs($this->user)->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = "{$this->account->sap_id};title;01.01.2000;income;subject;100.00";

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_expense()
    {
        $operation = FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->expenseType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test'
        ]);

        $response = $this->actingAs($this->user)->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = "{$this->account->sap_id};title;01.01.2000;expense;subject;-100.00";

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_checked()
    {
        $operation = FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->incomeType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test'
        ]);

        $response = $this->actingAs($this->user)->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = "{$this->account->sap_id};title;01.01.2000;income;subject;100.00";

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_lending()
    {
        $operation = FinancialOperation::factory()->create([
            'account_user_id' => $this->userWithPivot->pivot->id,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->lendingType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test'
        ]);

        $response = $this->actingAs($this->user)->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = "{$this->account->sap_id};title;01.01.2000;lending;subject;100.00";

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_with_all_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtered_export_with_some_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[3]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[1]}&to={$this->dates[2]}");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtered_export_with_no_data()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[2]}&to={$this->dates[3]}");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+0,$rows);
    }

    public function test_export_data_unbound_from_right()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[1]}");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_export_data_unbound_from_left()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?to={$this->dates[1]}");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtering_export_invalid_interval_causes_redirect()
    {
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[1]}&to={$this->dates[0]}");

        $response->assertStatus(302);
    }

    public function test_filtering_export_invalid_input_causes_redirect()
    {

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from=invalid");

        $response->assertStatus(302);
    }

    public function test_export_filename(){

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export");

        $response->assertStatus(200);
        $expected = 'attachment; filename=' . $this->account->getSanitizedSapId() . '_export.csv';
        $this->assertEquals($expected, $response->headers->get('content-disposition'));
    }

    public function test_export_filename_filtered(){

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[0]}&to={$this->dates[1]}");

        $response->assertStatus(200);
        $expected = "attachment; filename=" . $this->account->getSanitizedSapId() . "_export_{$this->fromClause}_01-01-2000_{$this->toClause}_01-01-2001.csv";
        $this->assertEquals($expected, $response->headers->get('content-disposition'));
    }

    public function test_export_filename_unbound_from_right(){

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?from={$this->dates[0]}");

        $response->assertStatus(200);
        $expected = "attachment; filename=" . $this->account->getSanitizedSapId() . "_export_{$this->fromClause}_01-01-2000.csv";
        $this->assertEquals($expected, $response->headers->get('content-disposition'));
    }

    public function test_export_filename_unbound_from_left(){

        $response = $this->actingAs($this->user)
            ->get("/accounts/{$this->account->id}/operations/export?to={$this->dates[1]}");

        $response->assertStatus(200);
        $expected = "attachment; filename=" . $this->account->getSanitizedSapId() . "_export_{$this->toClause}_01-01-2001.csv";
        $this->assertEquals($expected, $response->headers->get('content-disposition'));
    }

}
