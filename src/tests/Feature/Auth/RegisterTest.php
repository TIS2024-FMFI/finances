<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    private $email;
    private $emailRequired;

    private $ajaxHeaders;

    public function setUp(): void
    {
        parent::setUp();

        $this->email = App::isLocale('en')
            ? 'email'
            : trans('validation.attributes.email');

        $this->emailRequired = App::isLocale('en')
            ? trans('validation.required', [ 'attribute' => 'email' ])
            : trans('validation.custom.email.required');

        $this->ajaxHeaders = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];
    }
    
    public function test_that_only_ajax_requests_are_handled()
    {
        $response = $this->post('/register', [ 'email' => '' ]);

        $response
            ->assertStatus(500);
    }

    public function test_that_first_user_can_register()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $response = $this->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => '' ]
                            );

        $response
            ->assertStatus(422);
    }

    public function test_that_unauthenticated_user_cannot_register_new_user()
    {
        User::firstOrCreate([ 'email' => 'a@b.c' ]);

        $response = $this->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => '' ]
                            );

        $response
            ->assertStatus(403);
    }

    public function test_that_empty_email_is_rejected()
    {
        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);

        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => '' ]
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.email.0',
                $this->emailRequired
            );
    }

    public function test_that_invalid_email_is_rejected()
    {
        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);

        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => 'xxx' ]
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.email.0',
                trans('validation.email', [ 'attribute' => $this->email ])
            );
    }

    public function test_that_existing_email_is_rejected()
    {
        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
 
        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => $user->email ]
                            );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.email.0',
                trans('validation.unique', [ 'attribute' => $this->email ])
            );
    }

    public function test_that_new_user_with_unique_email_is_registered()
    {
        $user = User::firstOrCreate([ 'email' => 'a@b.c' ]);
        $email = 'x@x.x';
 
        $response = $this->actingAs($user)
                            ->withHeaders($this->ajaxHeaders)
                            ->post(
                                '/register',
                                [ 'email' => $email ]
                            );

        $response
            ->assertStatus(201);

        $created = User::where('email', $email)->first();
        $this->assertNotNull($created);

        $created->delete();
    }
}
