<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_ログインユーザーはレビュー投稿できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 4,
            'comment' => 'テスト用のコメント',
        ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews',[
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'テスト用のコメント'
        ]);
    }

    public function test_未ログインユーザーはレビュー投稿できない(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create(['user_id' => $user->id, 'title' => 'テストの本', 'author' => '著者', 'isbn' => '9784000000000']);

        $response = $this->post(route('reviews.store', $book),[
            'rating' => 4,
            'comment' => 'テスト用のコメント'
        ]);

        $response->assertRedirect('/login');
    }

    public function test_投稿者本人は既存の評価とコメントが初期値として表示された編集画面に遷移できる(): void
    {
        $this->withoutExceptionHandling();

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '面白いです。'
        ]);

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        $response->assertOk();

        $this->assertEquals(3, $review->rating);
        $this->assertEquals('面白いです。', $review->comment);
    }

    public function test_投稿者本人はバリデーション通過後にレビューを更新できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '面白いです。'
        ]);

        $update = [
            'rating'  => 2,
            'comment' => '読み直したら少し微妙でした',
        ];

        $response = $this->actingAs($user)->put(route('reviews.update', $review), $update);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'id'      => $review->id,
            'rating'  => 2,
            'comment' => '読み直したら少し微妙でした',
        ]);
    }

    public function test_他人のレビューを編集または更新しようとすると403認可エラーになる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $other = User::create([
            'name' => '他人',
            'email' => 'author@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $other->books()->create([
            'user_id' => $user->id,
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '面白いです。'
        ]);

        $response = $this->actingAs($other)->get(route('reviews.edit', $review));

        $responseUpdate = $this->actingAs($other)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '勝手に書き換え'
        ]);

        $response->assertStatus(403);
        $responseUpdate->assertStatus(403);
    }
}
