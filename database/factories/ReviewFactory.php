<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commentsByRating = [
            5 => [
                '友人から勧められて読みましたが、大満足の内容です。',
                '何度も読んでも面白い本です！ぜひ、みんなに読んでほしい！！！',
            ],
            4 => [
                'この本は友人にも勧めてみようかなと思います。',
                '何度読んでも新しい発見がある名作です。',
            ],
            3 => [
                '本の名前は、知っていましたが手に取ったのは初めてです。読みやすかったと思います！',
                'あまり読まないジャンルに今回は挑戦をしてみましたがとても面白かったと思います。',
            ],
            2 => [
                '世界にはいろんな本があるんだな〜と思いました。',
                '一回読んで、満足かな〜と思いました。',
            ],
            1 => [
                'あまり好みじゃなかったです。',
                '途中で飽きてしまった。',
            ],
        ];

        $rating = rand(1, 5);

        $comment = $commentsByRating[$rating][array_rand($commentsByRating[$rating])];

        return [
            'rating' => $rating,
            'comment' => $comment,
        ];
    }
}
