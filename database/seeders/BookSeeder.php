<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $genres =Genre::all()->keyBy('name');

        $books = [
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'published_date' => '1905-01-01', 'genres' => ['小説'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1', 'description' => '中学校の英語教師である珍野苦沙弥の家に飼われている猫である「吾輩」の視点から、珍野一家や、そこに出入りする人々の様子を風刺的に描いた作品。'],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'published_date' => '1936-10-01', 'genres' => ['ビジネス', '自己啓発'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2', 'description' => '「相手の立場に身を置き、心から尊重する」ことで良好な人間関係を築くための一冊。'],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'published_date' => '2012-06-23', 'genres' => ['技術書'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3', 'description' => '「読みやすいコード」を書くための実践的な技術を書いた作品。'],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'published_date' => '2013-08-30', 'genres' => ['ビジネス', '自己啓発'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4', 'description' => '時代や環境に左右されない「普遍的な原理原則」に基づき、人間的な成長を通じて真の成功と幸せを手に入れるための道筋を書いた一冊。'],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'published_date' => '1906-04-01', 'genres' => ['小説'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5', 'description' => 'ずる賢い同僚や生徒たちと大騒動を繰り広げる痛快なユーモア小説。'],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'published_date' => '2016-09-08', 'genres' => ['歴史', '科学'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6', 'description' => 'いかにして地球の支配者になれたのかを解き明かす一冊。'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'published_date' => '2017-12-18', 'genres' => ['技術書'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7', 'description' => '保守しやすく、読みやすく、変更しやすいコード（クリーンコード）」を書くための技術と心構えを解説した一冊。'],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'published_date' => '2013-12-13', 'genres' => ['自己啓発'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8' ,'description' => '心理学者アルフレッド・アドラーの思想「アドラー心理学」を、哲学者と悩める青年の対話形式で分かりやすく解説した一冊。'],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'published_date' => '2015-03-11', 'genres' => ['小説'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9' ,'description' => '「笑いとは何か」「人間とは何か」を深く問いかけながら別の道を歩んでいく姿を描いた青春小説。'],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'published_date' => '2019-01-11', 'genres' => ['ビジネス', '科学'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10', 'description' => '人間が無意識に抱く「10の思い込み」を指摘し、データと事実に基づいて客観的かつ正しく世界を見るスキルを教えてくれる一冊。'],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'published_date' => '2007-01-18', 'genres' => ['ビジネス', '歴史'], 'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11', 'description' => 'コンテナ輸送が世界経済に与えた影響を解説した一冊。'],
        ];

        foreach ($books as $data) {
            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'user_id' => $users->random()->id,
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['published_date'],
                    'description' => $data['description'],
                    'image_url' => $data['image_url'],
                ],
            );
            $genreIds = collect($data['genres'])->map(fn ($name) => $genres[$name]->id)->toArray();
            $book->genres()->sync($genreIds);
        }
    }
}
