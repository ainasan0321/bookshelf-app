<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;


    public function test_未ログインユーザーは書籍一覧にアクセスできる(): void
    {
        $this->get(route('books.index'))->assertOk();
    }

    public function test_認証ユーザーは書籍一覧を表示できる(): void
    {
        //準備
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        //検証
        $this->actingAs($user)->get(route('books.index'))->assertOk();
    }

    public function test_認証ユーザーは書籍をを作成し、ジャンルをつけられる(): void
    {
        //準備
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '技術書']);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍名',
            'author' => 'テスト著者名',
            'isbn' => '9784000000000',
            'published_date' => '2026-09-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $book = Book::where('title', 'テスト書籍名')->first();

        $this->assertNotNull($book);

        $response->assertRedirect(route('books.show', $book));

        //検証
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => 'テスト書籍名',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_タイトルがないと書籍を作成できない(): void
    {
        //準備
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '技術書']);

        $this->actingAs($user)->post(route('books.store'),
        [
            'title' => '',
            'author' => 'テスト著者名',
            'genres' => [$genre->id],

        ])->assertSessionHasErrors('title');

        //検証
        $this->assertDatabaseCount('books', 0);
    }

    public function test_書籍詳細を表示できる(): void
    {
        //準備
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '詳細テスト',
            'author' => 'テスト著者'
        ]);

        //検証
        $this->actingAs($user)
            ->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('詳細テスト');
    }

    public function test_所有者は自分の書籍を更新できる(): void
    {
        //準備
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genre = Genre::create(['name' => '技術書']);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '古いタイトル',
            'author' => '古い著者',
        ]);

        $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9784000000000',
            'published_date' => '2026-09-01',
            'genres' => [$genre->id],
        ])->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    public function test_所有者は自分の書籍を削除できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => '削除テスト',
            'author' => 'テスト著者',
        ]);

        $this->actingAs($user)
            ->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_ユーザーはキーワードを入力してタイトルに部分一致する書籍を検索できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $bookPhp = $user->books()->create([
            'title' => 'はじめてのPHP超入門',
            'author' => '著者A',
            'isbn' => '9784000000001'
        ]);

        $bookRuby = $user->books()->create([
            'title' => 'プログラミング',
            'author' => '著者B',
            'isbn' => '9784000000002'
        ]);

        $responseSearch = $this->actingAs($user)->get(route('books.index', ['keyword' => 'PHP']));

        $responseSearch->assertOk();
        $responseSearch->assertSee('はじめてのPHP超入門');
        $responseSearch->assertDontSee('プログラミング');
    }

    public function test_ユーザーはジャンルを選択してそのジャンルに属する書籍のみをフィルタリングできる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genreTech = Genre::create(['name' => '技術書']);
        $genreNovel = Genre::create(['name' => '小説']);

        $bookTech = $user->books()->create([
            'title' => 'リーダブルコード',
            'author' => '著者',
            'isbn' => '9784000000000'
        ]);

        $bookTech->genres()->attach($genreTech->id);

        $bookNovel = $user->books()->create([
            'title' => 'こころ',
            'author' => '夏目漱石',
            'isbn' => '9784000000001'
        ]);

        $bookNovel->genres()->attach($genreNovel->id);

        $response = $this->actingAs($user)->get(route('books.index', ['genre' => $genreTech->id]));

        $response->assertOk();

        $genreOnly = $response->original->getData()['books'];

        $this->assertTrue($genreOnly->contains($bookTech->id));
        $this->assertFalse($genreOnly->contains($bookNovel->id));
    }

    public function test_ユーザーはキーワードとジャンルを指定して書籍を正しく絞り込み検索できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $genreTech = Genre::create(['name' => '技術書']);
        $genreNovel = Genre::create(['name' => '小説']);

        $bookPhp = $user->books()->create([
            'title' => 'laravel',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $bookPhp->genres()->attach($genreTech->id);

        $bookNovel = $user->books()->create([
            'title' => 'こころ',
            'author' => '夏目漱石',
            'isbn' => '9784000000002'
        ]);

        $bookNovel->genres()->attach($genreNovel->id);

        $query = [
            'keyword' => 'lara',
            'genre' => $genreTech->id,
        ];

        $response = $this->actingAs($user)->get(route('books.index', $query));

        $response->assertOk();

        $books = $response->original->getData()['books'];

        $this->assertTrue($books->contains($bookPhp->id));
        $this->assertFalse($books->contains($bookNovel->id));
    }

    public function test_索キーワードやジャンルに全く一致する書籍がない場合は該当する書籍はありませんというメッセージを画面に表示する(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => 'テストタイトル',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $response = $this->actingAs($user)->get(route('books.index', ['keyword' => '世界']));

        $response->assertOk();
        $response->assertDontSee('テストタイトル');
        $response->assertSee('書籍が見つかりませんでした。');
    }

    public function test_ユーザーは書籍一覧を新着順および古い順で正しく並び替えて表示できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $newBook = $user->books()->create([
            'title' => '新しい本',
            'author' => '新しい著者',
            'isbn' => '9784000000001',
            'created_at' => '2026-09-09 00:00:00'
        ]);

        $oldBook = $user->books()->create([
            'title' => '古い本',
            'author' => '古い著者',
            'isbn' => '9784000000002',
            'created_at' => '2026-09-08 00:00:00'
        ]);

        $responseNew = $this->actingAs($user)->get(route('books.index', ['sort' => 'latest']));

        $responseNew->assertOk();

        $newBooks = $responseNew->original->getData()['books'];

        $this->assertEquals($newBook->id, $newBooks->first()->id);
        $this->assertEquals($oldBook->id, $newBooks->last()->id);
    }

    public function test_ユーザーは書籍一覧をタイトル文字列の昇順で正しく並び替えて表示できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $bookExample = $user->books()->create([
            'title' => 'いちご狩りの思い出',
            'author' => 'いちご',
            'isbn' => '9784000000001',
        ]);

        $book = $user->books()->create([
            'title' => 'アメリカの日々',
            'author' => 'モーター',
            'isbn' => '9784000000002',
        ]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'title']));

        $response->assertOk();

        $title = $response->original->getData()['books'];

        $this->assertEquals($book->id, $title->first()->id);
        $this->assertEquals($bookExample->id, $title->last()->id);
    }

    public function test_ユーザーは書籍をレビュー平均評価の高い順に並び替え未評価の書籍は下部に表示される(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $highRatingBook = $user->books()->create([
            'title' => '高い本',
            'author' => 'Hi',
            'isbn' => '9784000000001',
        ]);

        $lowRatingBook = $user->books()->create([
            'title' => '低い本',
            'author' => 'Lo',
            'isbn' => '9784000000002',
        ]);

        $nothingRatingBook = $user->books()->create([
            'title' => '未評価の本',
            'author' => '未',
            'isbn' => '9784000000003',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatingBook->id,
            'rating' => 5,
            'comment' => '最高'
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatingBook->id,
            'rating' => 2,
            'comment' => 'あんまりだった'
        ]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'rating']));
        $response->assertOk();

        $bookRating = $response->original->getData()['books'];

        $this->assertEquals($highRatingBook->id, $bookRating->first()->id);
        $this->assertEquals($nothingRatingBook->id, $bookRating->last()->id);
    }
}

