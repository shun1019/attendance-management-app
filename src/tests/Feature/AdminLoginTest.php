<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required_for_login()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1, // 管理者
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required_for_login()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_invalid_credentials_show_error()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->first('email'));
    }
}
