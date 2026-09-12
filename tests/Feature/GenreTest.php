<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_認証ユーザーはジャンル一覧画面を表示できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Genre::create(['name' => 'ファンタジー']);

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);

        $response->assertSee('ファンタジー');
    }

    public function test_認証ユーザーは新しいジャンルを作成できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ミステリー',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['name' => 'ミステリー']);
    }

    public function test_認証ユーザーはジャンル名を更新できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create([
            'name' => '更新前のジャンル名',
        ]);

        $update = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル名',
        ]);

        $update->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres',[
            'id' => $genre->id,
            'name' => '更新後のジャンル名',
        ]);
    }

    public function test_認証ユーザーはジャンル名を削除できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create([
            'name' => '削除用のジャンル名',
        ]);

        $destroy = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $destroy->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}
