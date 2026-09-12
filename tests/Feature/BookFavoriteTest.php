<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookFavoriteTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_ログインユーザーは書籍のお気に入り状態をトグルできる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => 'テスト用の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $responseAdded = $this->actingAs($user)->post(route('favorites.toggle', $book), []);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $responseRemoved = $this->actingAs($user)->post(route('favorites.toggle', $book), []);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
