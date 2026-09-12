<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_一覧は_data_と_meta_の構造を返し、ジャンルや平均評価も含める(): void
    {
        $this->withoutExceptionHandling();

        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'apiの本',
            'author' => 'apiの著者',
            'isbn' => '9784000000001'
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'author', 'genres', 'average_rating', 'reviews_count'
                ],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);

        $this->assertIsString($response->json('data.0.title'));
    }

    public function test_キーワードで絞り込める(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'laravelの世界',
            'author' => 'laravelの著者',
            'isbn' => '9784000000001'
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => '路線図',
            'author' => '鉄道会社',
            'isbn' => '9784000000002'
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'laravelの世界');
    }

    public function test_ジャンルで絞り込める(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $genreNovel = Genre::create(['name' => '小説']);
        $genreHistory = Genre::create(['name' => '歴史']);

        $bookNovel = Book::create(['user_id' => $user->id, 'title' => '小説の本', 'author' => '著者', 'isbn' => '9784000000001']);
        $bookHistory = Book::create(['user_id' => $user->id, 'title' => '歴史の本', 'author' => '著者', 'isbn' => '9784000000002']);

        //ジャンルを本に紐付ける。
        $bookNovel->genres()->sync([$genreNovel->id]);
        $bookHistory->genres()->sync([$genreHistory->id]);

        $response = $this->getJson("/api/v1/books?genre={$genreNovel->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => '小説の本']);
    }

    public function test_per_page_で件数を指定できる(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            Book::create(['user_id' => $user->id, 'title' => "本{$i}", 'author' => '著者', 'isbn' => "978400000000{$i}"]);
        }

        $response = $this->getJson('/api/v1/books?perPage=2');

        $response->assertStatus(200);

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('meta.per_page', 2);

        $response->assertJsonPath('meta.total', 5);
    }

    public function test_書籍詳細を取得できる(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create(['user_id' => $user->id, 'title' => 'テストの本', 'author' => '著者', 'isbn' => '9784863327641']);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);

        $response->assertJsonPath('data.title', 'テストの本');

        $response->assertJsonPath('data.author', '著者');
    }

    public function test_存在しないidは_404_のjsonを返す(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }

    public function test_書籍を新規登録できる(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '歴史']);

        $payload = [
            'title' => 'API登録書籍',
            'author' => 'API著者',
            'isbn' => '9784000000000',
            'published_date' => '2026-09-01',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/books', $payload);

        $response->assertStatus(201);

        $response->assertJsonPath('data.title', 'API登録書籍');

        $this->assertDatabaseHas('books', ['title' => 'API登録書籍']);
    }

    public function test_不正な入力は_422_を返す(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/books', [
            'title' => '',
            'author' => '著者',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['title', 'genres']);
    }

    public function test_書籍を更新できる(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '歴史']);

        $book = Book::create(['user_id' => $user->id, 'title' => '更新前テストの本', 'author' => '更新前著者', 'isbn' => '9784000000000']);

        $payload = [
            'title' => '更新後テストの本',
            'author' => '更新後著者',
            'isbn' => '9784000000000',
            'published_date' => '2026-09-01',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertStatus(200);

        $response->assertJsonPath('data.title', '更新後テストの本');
    }

    public function test_書籍を削除すると_204_を返す(): void
    {
        $user = User::create([
            'name' => 'apiテストユーザー',
            'email' => 'apitest@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create(['user_id' => $user->id, 'title' => '削除用テストの本', 'author' => '著者', 'isbn' => '9784000000000']);

        $response = $this->actingAs($user)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
