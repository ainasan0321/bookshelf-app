<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_ユーザーが正しく登録できる(): void
    {
        $user = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $user);

        $response->assertRedirect('/books');

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_ユーザーは正しい資格情報でログインできる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $response = $this->post('/login', $credentials);

        $response->assertRedirect('/books');

        $this->assertAuthenticatedAs($user);
    }

    public function test_ユーザーは間違ったパスワードでログインできない(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => 'test-password',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_ログイン済みのユーザーはログアウトできる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)
            ->post('/logout');

        $response->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_未認証ユーザーは新規登録画面にアクセスできない(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect('/login');
    }

    public function test_メールアドレスが未入力だとユーザー登録に失敗する(): void
    {
        $user = [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $responseValidationError = $this->post('register', $user);

        $responseValidationError->assertSessionHasErrors('email');
    }
}
