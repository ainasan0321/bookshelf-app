<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function store(StoreReviewRequest $request, Book $book) :RedirectResponse
    {
        $validated = $request->validated();

        $user = auth()->user();

        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    public function update(UpdateReviewRequest $request, Review $review) :RedirectResponse
    {
        $this->authorize('update', $review);

        $validated = $request->validated();
        $rating = $review->rating;

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    public function toggle(Review $review) :RedirectResponse
    {
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return back()->with('success', 'いいねを更新しました。');
    }

    public function destroy(Review $review) :RedirectResponse
    {
        $this->authorize('delete', $review);

        $user = auth()->user();
        $review->delete();

        return back()->with('success', 'レビューを削除しました');
    }
}
