<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧（キーワード検索・ジャンルフィルタ・ソート対応）
     */
    public function index(): View
    {
        $keyword = request('keyword');
        $genreId = request('genre');
        $sort    = request('sort', 'newest');

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($genreId) {
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
        }

        match ($sort) {
            'oldest' => $query->oldest(),
            'title'  => $query->orderBy('title'),
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByRaw('reviews_avg_rating IS NULL'),
            default  => $query->latest(),
        };

        $books  = $query->paginate(10)->withQueryString();
        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録フォームを表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = Book::create([
            'user_id'        => auth()->id(),
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn ?: null,
            'published_date' => $request->published_date ?: null,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細を表示する
     */
    public function show(Book $book): View
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示する
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新する
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update([
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn ?: null,
            'published_date' => $request->published_date ?: null,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return redirect()->route('books.show', $book)->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->reviews->each(fn ($review) => $review->reviewLikes()->delete());
        $book->reviews()->delete();
        $book->favorites()->delete();
        $book->genres()->detach();
        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }
}
