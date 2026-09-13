<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_メール確認日時が自動で_carbonオブジェクトに変換される(): void
    {
        // 準備
        $user = new User;

        // 実行
        $user->email_verified_at = '2026-08-31 12:00:00';

        // 検証
        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
        $this->assertEquals('2026-08-31 12:00:00', $user->email_verified_at->format('Y-m-d H:i:s'));
    }

    public function test_パスワードやトークンの不表示になっている(): void
    {
        // 準備
        $user = new User;

        // 実行
        $hidden = $user->getHidden();

        // 検証
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);

    }

    public function test_正しいリレーションを返す(): void
    {
        // 準備
        $user = new User;

        // 実行
        $relationBook = $user->books();

        // 検証
        $this->assertInstanceOf(HasMany::class, $relationBook);

        // 実行
        $relationFavorite = $user->favoriteBooks();

        // 検証
        $this->assertInstanceOf(BelongsToMany::class, $relationFavorite);
    }

    public function test_ユーザーが一括で安全にセットできる(): void
    {
        // 準備
        $user = new User([
            'name' => 'テストネーム',
            'email' => 'test@example.com',
        ]);

        // 検証
        $this->assertEquals('テストネーム', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }
}
