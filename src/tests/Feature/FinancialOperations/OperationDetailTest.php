<?php

namespace Tests\Feature\FinancialOperations;

use App\Http\Controllers\FinancialOperations\GeneralOperationController;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationDetailTest extends TestCase
{
    use DatabaseTransactions;

    private Model $user, $userWithPivot, $account, $type, $lendingType;
    private array $headers;
    private GeneralOperationController $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(['email' => 'a@b.c']);
        $this->account = Account::factory()->hasAttached($this->user, [ 'account_title' => 'title' ])->create();
        $this->userWithPivot = $this->account->users->where('id', $this->user->id)->first();

        $this->type = OperationType::firstOrCreate(['name' => 'type', 'lending' => false]);
        $this->lendingType = OperationType::firstOrCreate(['name' => 'lending', 'lending' => true]);

        $this->headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->controller = new GeneralOperationController;

    }

    public function test_operation_data(){

        $operation = FinancialOperation::factory()->create(['account_user_id' => $this->userWithPivot->pivot->id, 'operation_type_id' => $this->type]);

        $response = $this->actingAs($this->user)->get("/operations/$operation->id");
        $content = $response->json();

        $this->assertEquals($this->userWithPivot->pivot->id, $content['operation']['account_user_id']);
        $this->assertEquals($this->type->id, $content['operation']['operation_type_id']);
    }

    public function test_cant_view_nonexistent_operation(){

        $response = $this->actingAs($this->user)->get("/operations/99999");
        $response->assertStatus(404);
    }

    public function test_attachment_download(){

        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt');
        $dir = FinancialOperation::getAttachmentsDirectoryPath($this->user);
        $path = Storage::putFile($dir, $file);

        $operation = FinancialOperation::factory()
            ->create([
                'title' => 'operation',
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->type,
                'attachment' => $path
                ]);

        $response = $this->actingAs($this->user)->get("/operations/$operation->id/attachment");

        $response->assertStatus(200);
        $response->assertDownload();

        $attachmentClause = trans('files.attachment');

        $this->assertEquals("attachment; filename=operation_$attachmentClause.txt",
            $response->headers->get('content-disposition'));

        Storage::fake('local');
    }

    public function test_cant_download_nonexistent_attachment(){

        Storage::fake('local');

        $operation = FinancialOperation::factory()
            ->create([
                'account_user_id' => $this->userWithPivot->pivot->id,
                'operation_type_id' => $this->type,
                'attachment' => ''
            ]);

        $response = $this->actingAs($this->user)->get("/operations/$operation->id/attachment");

        $response->assertStatus(500);
    }

}
