<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create([
            'user_id'        => auth()->id(),
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return redirect()->route('books.index')->with('success', '書籍を登録しました。');
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);

        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update([
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました。');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->reviews->each(fn($review) => $review->reviewLikes()->delete());
        $book->reviews()->delete();
        $book->favorites()->delete();
        $book->genres()->detach();
        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }
}
