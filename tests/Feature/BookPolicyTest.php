<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPolicyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_更新と削除は所有者だけが許可される(): void
    {
        $testUser = User::create([
            'name' => '所有者',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $testOther = User::create([
            'name' => '他人',
            'email' => 'testother@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $testUser->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $this->assertTrue($testUser->can('update', $book));
        $this->assertTrue($testUser->can('delete', $book));
        $this->assertFalse($testOther->can('update', $book));
        $this->assertFalse($testOther->can('c', $book));
    }

    public function test_他人は書籍を更新できない(): void
    {
        $testUser = User::create([
            'name' => '所有者',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $testOther = User::create([
            'name' => '他人',
            'email' => 'testother@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $testUser->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $genre = Genre::create(['name' => '歴史']);

        $response = $this->actingAs($testOther)->putJson("/api/v1/books/{$book->id}", [
            'title'  => '更新タイトル',
            'author' => '更新著者',
            'genres' => [$genre->id],
        ]);

        $response->assertForbidden();
    }

    public function test_他人は書籍の編集画面を開けない(): void
    {
        $testUser = User::create([
            'name' => '所有者',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $testOther = User::create([
            'name' => '他人',
            'email' => 'testother@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $testUser->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $response = $this->actingAs($testOther)->get(route('books.edit', $book))->assertForbidden();
    }

    public function test_他人は書籍を削除できない(): void
    {
        $testUser = User::create([
            'name' => '所有者',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $testOther = User::create([
            'name' => '他人',
            'email' => 'testother@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $testUser->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $response = $this->actingAs($testOther)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_未ログインのゲストは書籍の登録と更新と削除がすべて制限される(): void
    {
        $user = User::create([
            'name' => '所有者',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $responsePostGuest = $this->postJson('/api/v1/books', ['title' => 'ゲスト登録']);
        $responsePutGuest = $this->putJson("/api/v1/books/{$book->id}", ['title' => 'ゲスト更新']);
        $responseDeleteGuest = $this->deleteJson("/api/v1/books/{$book->id}");

        $responsePostGuest->assertStatus(401);
        $responsePutGuest->assertStatus(401);
        $responseDeleteGuest->assertStatus(401);
    }

    public function test_存在しない書籍idに対して他人が操作を試みた場合は403ではなく404を返す(): void
    {
        $other = User::create([
            'name' => '所有者',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $responsePutMissing = $this->actingAs($other)->putJson('/api/v1/books/99999', ['title' => '勝手に更新']);

        $responseDeleteMissing = $this->actingAs($other)->deleteJson('/api/v1/books/99999');

        $responsePutMissing->assertStatus(404);
        $responseDeleteMissing->assertStatus(404);
    }

    public function test_書籍の一覧と詳細の閲覧は所有者以外の他人でも許可される(): void
    {
        $user = User::create([
            'name' => '所有者',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);

        $other = User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password')
        ]);

        $book = $user->books()->create([
            'title' => '所有者の本',
            'author' => '著者',
            'isbn' => '9784000000003'
        ]);

        $response = $this->actingAs($other)->getJson('/api/v1/books');
        $responseShow = $this->actingAs($other)->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();
        $responseShow->assertOk();
    }
}