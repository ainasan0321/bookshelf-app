<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function index(): View
    {
        $rankedBooks = Book::withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->has('reviews')
        ->orderByDesc('reviews_avg_rating')
        ->orderByDesc('reviews_count')
        ->take(10)
        ->get();
        return view('ranking.index', compact('rankedBooks'));
    }
}
