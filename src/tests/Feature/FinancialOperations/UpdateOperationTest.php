<?php

namespace Tests\Feature\FinancialOperations;

use App\Http\Controllers\FinancialOperations\GeneralOperationController;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\Lending;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateOperationTest extends TestCase
{
    use DatabaseTransactions;

    private Model $user, $userWithPivot, $account, $type, $lendingType, $repaymentType;
    private array $headers;
    private GeneralOperationController $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(['email' => 'a@b.c']);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'account' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

        $this->type = OperationType::firstOrCreate(['name' => 'type', 'lending' => false]);
        $this->lendingType = OperationType::firstOrCreate(['name' => 'lending', 'lending' => true]);
        $this->repaymentType = OperationType::firstOrCreate(['name' => 'repayment', 'repayment' => true]);

        $this->headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->controller = new GeneralOperationController;

    }

    public function test_update_operation_form_data(){
        $op = FinancialOperation::factory()
                    ->create([
                        'account_user_id' => $this->userWithPivot->pivot->id,
                        'operation_type_id' => $this->lendingType
                    ]);
        Lending::factory()->create(['id' => $op]);

        $response = $this->actingAs($this->user)
                            ->withHeaders($this->headers)
                            ->get(
                                '/operations/' . $op->id . '/update'
                            );

        $response->assertStatus(200);
        $response
            ->assertJsonPath('operation.id', $op->id);

    }

    public function test_update_operation(){

        $operation = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'title' => 'original',
                'sum' => 100,
                'operation_type_id' => $this->type
            ]);

        $patchData = [
            'title' => 'updated',
            'sum' => 50
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", $patchData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('financial_operations', [
            'id' => $operation->id,
            'title' => 'updated',
            'sum' => 50
        ]);
    }

    public function test_cannot_update_with_empty_data(){

        $operation = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'title' => 'original',
                'sum' => 100,
                'operation_type_id' => $this->type
            ]);

        $patchData = [
            'title' => ''
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", $patchData);

        $response->assertStatus(422);
    }

    public function test_cannot_update_return_date_to_be_earlier_than_operation_date(){

        $loan = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->lendingType
            ]);
        $lending = Lending::factory()->create(['id' => $loan]);

        $patchData = [
            'date' => $loan->date,
            'expected_date_of_return' => $loan->date->subDay()
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$loan->id", $patchData);

        $response->assertStatus(422);
    }

    public function test_cannot_update_nonexisting_operation(){

        $patchData = [
            'title' => 'updated'
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch('/operations/9999', $patchData);

        $response->assertStatus(404);
    }

    public function test_update_operation_creates_file(){

        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt');

        $operation = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->type,
                'attachment' => null
            ]);

        $patchData = [
            'attachment' => $file
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", $patchData);

        $response->assertStatus(200);
        $operation->refresh();
        $path = $operation->attachment;
        Storage::disk('local')->assertExists($path);

        Storage::fake('local');

    }

    public function test_update_operation_replaces_file(){

        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt');

        $dir = FinancialOperation::getAttachmentsDirectoryPath($this->user);
        $oldPath = Storage::putFile($dir, $file);

        $operation = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->type,
                'attachment' => $oldPath
            ]);

        $newFile = UploadedFile::fake()->create('test.txt');
        $patchData = [
            'attachment' => $newFile
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", $patchData);

        $response->assertStatus(200);
        $operation->refresh();
        $newPath = $operation->attachment;
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertMissing($oldPath);

        Storage::fake('local');
    }

    public function test_update_lending_expected_date()
    {
        $loan = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->lendingType
            ]);

        $lending = Lending::factory()->create(
            [
                'id' => $loan,
                'expected_date_of_return' => now()->format('Y-m-d')
            ]);

        $laterDate = now()->addDay()->format('Y-m-d');
        $patchData = [
            'expected_date_of_return' => $laterDate
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$loan->id", $patchData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'expected_date_of_return' => $laterDate
        ]);

    }

    public function test_cannot_update_loan_to_be_later_than_repayment()
    {
        $loan = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->lendingType
            ]);
        Lending::factory()->create(['id' => $loan]);

        $repayment = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->repaymentType,
                'date' => $loan->date->addDay()
            ]);
        Lending::factory()->create(
            [
                'id' => $repayment,
                'previous_lending_id' => $loan
            ]);

        $patchData = [
            'date' => $repayment->date->addDay()
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$loan->id", $patchData);

        $response->assertStatus(422);
    }

    public function test_loan_update_updates_repayment()
    {
        $loan = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->lendingType,
                'attachment' => null
            ]);
        Lending::factory()->create(['id' => $loan]);

        $repaymetData = $loan->getAttributes();
        $repaymetData['date'] = $loan->date->addDays(2);
        unset($repaymetData['id']);

        $repayment = FinancialOperation::factory()->create($repaymetData);
        Lending::factory()->create(
            [
                'id' => $repayment,
                'previous_lending_id' => $loan
            ]);

        $patchData = [
            'title' => 'novy nazov',
            'subject' => 'novy subjekt',
            'sum' => $repaymetData['sum'] + 100,
            'date' => $loan->date->addDay(),
            'expected_date_of_return' => now()
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$loan->id", $patchData);

        $response->assertStatus(200);

        $oldDate = $repayment->date;
        $repayment->refresh();

        $this->assertEquals($patchData['title'], $repayment->title);
        $this->assertEquals($patchData['subject'], $repayment->subject);
        $this->assertEquals($patchData['sum'], $repayment->sum);
        $this->assertEquals($oldDate, $repayment->date);
    }

    public function test_cannot_update_repayment()
    {
        $repayment = FinancialOperation::factory()->create(
            [
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->repaymentType
            ]);

        $patchData = [
            'title' => 'updated'
        ];

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$repayment->id", $patchData);

        $response->assertStatus(500);
    }

    /*public function test_check_operation()
    {

        $operation = FinancialOperation::factory()->create(
            ['account_user_id' => $this->userWithPivot->pivot->id, 'checked' => false, 'operation_type_id' => $this->type]);

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", ['checked' => true]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('financial_operations', [
            'id' => $operation->id,
            'checked' => true
        ]);
    }

    /*public function test_uncheck_operation()
    {

        $operation = FinancialOperation::factory()->create(
            ['account_user_id' => $this->userWithPivot->pivot->id, 'checked' => true, 'operation_type_id' => $this->type]);

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", ['checked' => false]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('financial_operations', [
            'id' => $operation->id,
            'checked' => false
        ]);
    }

    public function test_cannnot_check_lending()
    {

        $operation = FinancialOperation::factory()->create(
            ['account_user_id' => $this->userWithPivot->pivot->id, 'checked' => false, 'operation_type_id' => $this->lendingType]);
        Lending::factory()->create(['id' => $operation]);

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", ['checked' => true]);

        $response->assertStatus(500);

    }

    public function test_cannnot_check_repayment()
    {

        $operation = FinancialOperation::factory()->create(
            ['account_user_id' => $this->userWithPivot->pivot->id, 'checked' => false, 'operation_type_id' => $this->repaymentType]);
        Lending::factory()->create(['id' => $operation]);

        $response = $this->actingAs($this->user)->withHeaders($this->headers)
            ->patch("/operations/$operation->id", ['checked' => true]);

        $response->assertStatus(500);

    }*/

}
