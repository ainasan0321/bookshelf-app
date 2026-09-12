<?php

namespace Tests\Unit;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class GenreModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    public function test_正しいリレーションを返す(): void
    {
        //準備
        $genre = new Genre();

        //実行
        $relation = $genre->books();

        //検証
        $this->assertInstanceOf(BelongsToMany::class, $relation);
    }

    public function test_ジャンル名が一括で安全にセットできる(): void
    {
        //準備
        $genre = new Genre([
            'name' => 'テストジャンル'
        ]);

        //検証
        $this->assertEquals('テストジャンル', $genre->name);
    }
}
