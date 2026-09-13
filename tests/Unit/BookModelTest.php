<?php

namespace Tests\Unit;

use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_出版日が自動で_carbonオブジェクトに変換される(): void
    {
        // 準備
        $book = new Book;

        // 実行
        $book->published_date = '2026-08-31';

        // 検証
        $this->assertInstanceOf(Carbon::class, $book->published_date);
        $this->assertEquals('2026-08-31', $book->published_date->format('Y-m-d'));
    }

    public function test_正しいリレーションを返す(): void
    {
        // 準備
        $book = new Book;

        // 実行
        $relationReviews = $book->reviews();

        // 検証
        $this->assertInstanceOf(HasMany::class, $relationReviews);

        // 実行
        $relationUser = $book->user();

        // 検証
        $this->assertInstanceOf(BelongsTo::class, $relationUser);

        // 実行
        $relationGenres = $book->genres();

        // 検証
        $this->assertInstanceOf(BelongsToMany::class, $relationGenres);
    }

    public function test_指定した項目が一括で安全にセットできる(): void
    {
        // 準備
        $book = new Book([
            'title' => 'テスト',
            'author' => 'テストの名前',
            'isbn' => '0000000000123',
        ]);

        // 検証
        $this->assertEquals('テスト', $book->title);
        $this->assertEquals('テストの名前', $book->author);
        $this->assertEquals('0000000000123', $book->isbn);
    }

    public function test_出版日がnullでも、エラーにならずにnullとして保持できる(): void
    {
        // 準備
        $book = new Book;

        // 実行
        $book->published_date = null;

        // 検証
        $this->assertNull($book->published_date);
    }
}
