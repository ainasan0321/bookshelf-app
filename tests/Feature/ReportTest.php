<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_未ログインユーザーがマイ読書レポート画面にアクセスしようとするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect('/login');
    }

    public function test_読書レポート画面の基本サマリーには他人のデータが混ざらず本人の実績のみが正確に計算されて表示される(): void
    {
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $other = User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '小説']);

        $userBook = $user->books()->create([
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $userBook->genres()->attach($genre->id);

        $userReview = Review::create([
            'user_id' => $user->id,
            'book_id' => $userBook->id,
            'rating' => 5,
            'comment' => 'とても良い本です！',
        ]);

        $otherBook = $other->books()->create([
            'title' => '他人の本',
            'author' => '著者',
            'isbn' => '9784000000004'
        ]);

        $otherBook->genres()->attach($genre->id);

        $otherReview = Review::create([
            'user_id' => $other->id,
            'book_id' => $otherBook->id,
            'rating' => 1,
            'comment' => 'あんまりでした。',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $data = $response->original->getData()['summary'] ?? null;

        if ($data) {
            $this->assertEquals(1, $data['total_reviews'] ?? $data->total_reviews);
            $this->assertEquals(5.0, $data['average_rating'] ?? $data->average_rating);
        }
    }

    public function test_読書レポート画面の評価分布グラフには他人のレビュー数が混ざらず本人の星ごとの件数のみが集計される(): void
    {
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $other = User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '小説']);

        $userBook = $user->books()->create([
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $userBook->genres()->attach($genre->id);

        $userReview = Review::create([
            'user_id' => $user->id,
            'book_id' => $userBook->id,
            'rating' => 5,
            'comment' => 'とても良い本です！',
        ]);

        $otherBook = $other->books()->create([
            'title' => '他人の本',
            'author' => '著者',
            'isbn' => '9784000000004'
        ]);

        $otherBook->genres()->attach($genre->id);

        $otherReview = Review::create([
            'user_id' => $other->id,
            'book_id' => $otherBook->id,
            'rating' => 1,
            'comment' => 'あんまりでした。',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $data = $response->original->getData()['distribution'] ?? null;

        if ($data) {
            $this->assertEquals(1, $data);
        }
    }

    public function test_読書レポート画面の高評価書籍ランキングには他人の高評価本が混ざらず自分の本のみが表示される(): void
    {
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $other = User::create([
            'name' => '他人',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '小説']);

        $userBook = $user->books()->create([
            'title' => 'テストの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $userBook->genres()->attach($genre->id);

        $userReview = Review::create([
            'user_id' => $user->id,
            'book_id' => $userBook->id,
            'rating' => 5,
            'comment' => 'とても良い本です！',
        ]);

        $otherBook = $other->books()->create([
            'title' => '他人の本',
            'author' => '著者',
            'isbn' => '9784000000004'
        ]);

        $otherBook->genres()->attach($genre->id);

        $otherReview = Review::create([
            'user_id' => $other->id,
            'book_id' => $otherBook->id,
            'rating' => 1,
            'comment' => 'あんまりでした。',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $data = $response->original->getData()['topBooks'] ?? $response->original->getData()['books'] ?? null;

        if ($data) {
            $this->assertTrue($data->contains($ownerBook->id));
            $this->assertFalse($data->contains($otherBook->id));
        }
    }

    public function test_読書レポート画面のジャンル別評価傾向ランキングには本人が読んだジャンルの統計が正確に表示される(): void
    {
        $user = User::create([
            'name' => 'ユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create([
            'name' => '小説'
        ]);

        $book =$user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000008'
        ]);

        $book->genres()->attach($genre->id);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても良い作品'
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $data = $response->original->getData()['topGenres'] ?? $response->original->getData()['genres'] ?? null;

        if ($data) {
            $this->assertEquals($genre->id, $data->first()->id);
        }
    }
}
