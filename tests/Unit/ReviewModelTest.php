<?php

namespace Tests\Unit;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_正しいリレーションを返す(): void
    {
        // 準備
        $review = new Review;

        // 実行
        $relationUser = $review->user();

        // 検証
        $this->assertInstanceOf(BelongsTo::class, $relationUser);

        // 実行
        $relationBook = $review->book();

        // 検証
        $this->assertInstanceOf(BelongsTo::class, $relationBook);

        // 実行
        $relationLikes = $review->likedByUsers();

        // 検証
        $this->assertInstanceOf(BelongsToMany::class, $relationLikes);
    }

    public function test_レビューが一括で安全にセットできる(): void
    {
        // 準備
        $review = new Review([
            'user_id' => 1,
            'book_id' => 2,
            'rating' => 3,
            'comment' => 'テストコメント。',
        ]);

        // 検証
        $this->assertEquals(1, $review->user_id);
        $this->assertEquals(2, $review->book_id);
        $this->assertEquals(3, $review->rating);
        $this->assertEquals('テストコメント。', $review->comment);
    }
}
