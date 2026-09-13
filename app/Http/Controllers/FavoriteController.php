<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $user = auth()->user();

        $result = $user->favoriteBooks()->toggle($book->id);

        if (in_array($book->id, $result['attached'])) {
            $message = 'お気に入りに登録しました。';
        } else {
            $message = 'お気に入りを解除しました。';
        }

        return back()->with('success', $message);
    }
}
