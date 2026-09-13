<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_ログインユーザーはレビューにいいねをトグルできる(): void
    {
        $user = User::create([
            'name' => '投稿者',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $reviewLiker = User::create([
            'name' => 'いいねする人',
            'email' => 'liker@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create(['user_id' => $user->id, 'title' => 'テストの本', 'author' => '著者', 'isbn' => '9784000000001']);

        $review = Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'テスト用のコメント',
        ]);

        $responseAdded = $this->actingAs($reviewLiker)->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $reviewLiker->id,
            'review_id' => $review->id,
        ]);

        $responseRemoved = $this->actingAs($reviewLiker)->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $reviewLiker->id,
            'review_id' => $review->id,
        ]);
    }
}
