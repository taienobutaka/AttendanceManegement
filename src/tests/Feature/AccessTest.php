<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_checks_homepage_access()
    {
        $response = $this->get('/');

        $response->assertStatus(302); // ホームページがリダイレクトされることを確認（未ログインの場合）
    }

    /** @test */
    public function it_checks_login_page_access()
    {
        $response = $this->get('/login');

        $response->assertStatus(200); // ログインページが正常にアクセスできることを確認
    }

    /** @test */
    public function it_checks_register_page_access()
    {
        $response = $this->get('/register');

        $response->assertStatus(200); // 登録ページが正常にアクセスできることを確認
    }

    /** @test */
    public function it_checks_attendance_list_access()
    {
        $response = $this->get('/attendance');

        $response->assertStatus(302); // 勤怠リストがリダイレクトされることを確認（未ログインの場合）
    }
}
