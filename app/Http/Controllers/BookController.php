<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Book::query();

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $sort = $request->input('sort', 'latest');

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');

        } elseif ($sort === 'title') {
            $query->orderBy('title', 'asc');

        } elseif ($sort === 'rating') {
            $query->withAvg('reviews', 'rating')
                ->orderByRaw('ISNULL(reviews_avg_rating) ASC')
                ->orderBy('reviews_avg_rating', 'desc');
        }

        $books = $query->paginate(10)->withQueryString();
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function show(string $id)
    {
        $book = Book::with(['genres', 'reviews.user'])->findOrFail($id);
        $reviews = $book->reviews;

        return view('books.show', compact('book', 'reviews'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $book = $request->user()->books()->create([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres']);

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました。');
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        if ($book->published_date) {
            session()->flash('_old_input', [
                'published_date' => $book->published_date->format('Y-m-d'),
            ]);
        }

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }

    public function search($isbn): JsonResponse
    {

        $fullUrl = 'https://googleapis.com'.$isbn;

        $rawResponse = @file_get_contents($fullUrl);

        if ($rawResponse === false) {
            return response()->json(['error' => '書籍情報の取得に失敗したか、該当する本が見つかりませんでした。'], 404);
        }

        $bookData = json_decode($rawResponse, true);

        if (($bookData['totalItems'] ?? 0) === 0 || ! isset($bookData['items'])) {
            return response()->json(['error' => '該当する書籍情報が見つかりませんでした。'], 404);
        }

        $volumeInfo = $bookData['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $volumeInfo['title'] ?? 'タイトル不明',
            'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '著者不明',
            'description' => $volumeInfo['description'] ?? '',
            'published_date' => $volumeInfo['publishedDate'] ?? '',
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
        ]);
    }
}
