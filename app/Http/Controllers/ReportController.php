<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Enums\ReadingPlanStatus;
use App\Models\Review;
use App\Models\ReadingPlan;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request) :View
    {
        //ログインしているユーザーのIDを取得
        $userId = auth()->id();

        //総レビュー数
        //自分のレビューの関連する書式データも一緒にまとめてDBから取得
        $myReviews = Review::where('user_id', $userId)->with('book')->get();

        //読了冊数
        //取得したレビューの「総件数」をカウント（総レビュー数）
        $totalReviews = $myReviews->count();

        //読書計画から、自分が「読了」かつ「完了日が入ってる」データの「本のID」を重複なしで数える
        $uniqueBooksCount = ReadingPlan::where('user_id', $userId)
            ->where('status', ReadingPlanStatus::Completed->value) //読了
            ->whereNotNull('completed_at') //完了日がある
            ->pluck('book_id') //本のIDだけ
            ->unique() //重複したIDを消す
            ->count();

        //平均評価
        //自分の全レビューの星の平均値を計算
        $averageRating = $myReviews->avg('rating') ?? '0';

        //評価分布
        //0番目のカウンターを全て0件で初期化
        $distributionArray = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];

        //自分のレビューを1件ずつ取り出して該当する星の箱のカウントにプラス1
        foreach ($myReviews as $review) {
            $ratingIndex = $review->rating - 1;
            if (isset($distributionArray[$ratingIndex])) {
                $distributionArray[$ratingIndex]++;
            }
        }

        //配列をコレクションに変換
        $ratingDistribution = collect($distributionArray);

        //高評価書籍 TOP5
        $topRatedBooks = $myReviews
            ->where('rating', '>=', 4) //星4以上のレビューだけ
            ->sortByDesc('rating') //評価が高い順
            ->take(5) //5個だけ
            ->map(function ($review) {
                return [
                    'id'     => $review->book_id,
                    'title'  => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->values();

        //データベースに直接届く生のSQL文を使って、ジャンルごとの平均点と件数を集計
        $genreStats = DB::table('reviews')
            ->join('book_genre', 'reviews.book_id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $userId)
            ->selectRaw('
                genres.id,
                genres.name,
                COUNT(DISTINCT reviews.book_id) as count,
                AVG(reviews.rating) as average_rating
            ')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        //配列を変換
        $genreRatings = $genreStats->map(function ($genre) {
            return (array) $genre;
        });

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $uniqueBooksCount,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
