<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_正しい情報でログインすると書籍一覧に遷移する(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('books.index'));
    }

    public function test_ログイン画面に会員登録へのリンクが表示される(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee(route('register'), false);
    }

    // 異常系
    public function test_メールアドレスを空にするとバリデーションエラーになる(): void
    {
        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_パスワードを空にするとバリデーションエラーになる(): void
    {
        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_登録されていないメールアドレスでログインするとバリデーションエラーになる(): void
    {
        $response = $this->post('/login', [
            'email'    => 'notregistered@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
