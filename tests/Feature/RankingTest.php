<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_認証ユーザーはランキングを表示できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => 'ランキングの本',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $response = $this->actingAs($user)->get(route('ranking.index'));

        $response->assertOk();
    }

    public function test_レビュー平均評価のtop10書籍が降順かつ最大10件で表示される(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $bookHighRating = $user->books()->create([
            'title' => '評価が高い本',
            'author' => '著者',
            'isbn' => '9784000000001',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $bookHighRating->id,
            'rating' => 5,
            'comment' => '最高'
        ]);

        $bookLowRating = $user->books()->create([
            'title' => '普通の本',
            'author' => '著者',
            'isbn' => '9784000000002',
        ]);
        
        Review::create([
            'user_id' => $user->id,
            'book_id' => $bookLowRating->id,
            'rating' => 3,
            'comment' => '普通でした'
        ]);

        for ($i = 3; $i <= 11; $i++) {

            $isbnCode = "9784000000" . sprintf('%03d', $i);

            $otherBook = $user->books()->create([
                'title'  => "その他の本{$i}",
                'author' => '著者',
                'isbn'   => $isbnCode
            ]);

            Review::create([
                'user_id' => $user->id,
                'book_id' => $otherBook->id,
                'rating'  => 4,
                'comment' => 'レビュー付き'
            ]);
        }

        $responseTopTen = $this->actingAs($user)->get(route('ranking.index'));

        $responseTopTen->assertOk();

        $viewBooks = $responseTopTen->original->getData()['rankedBooks'];

        $this->assertCount(10, $viewBooks);

        $this->assertEquals($bookHighRating->id, $viewBooks->first()->id);
    }
}
