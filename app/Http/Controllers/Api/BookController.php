<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookIndexRequest;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index(BookIndexRequest $request)
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('author', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->genre) {
            $query->whereHas('genres', fn($q) => $q->where('genres.id', $request->genre));
        }

        $books = $query->latest()->paginate($request->per_page ?? 10);

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return new BookDetailResource($book);
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create([
            'user_id'        => $request->user_id,
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return (new BookDetailResource($book->load('genres')))->response()->setStatusCode(201);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update([
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->input('genres', []));

        return new BookDetailResource($book->load('genres'));
    }

    public function destroy(Book $book)
    {
        $book->reviews->each(fn($review) => $review->reviewLikes()->delete());
        $book->reviews()->delete();
        $book->favorites()->delete();
        $book->genres()->detach();
        $book->delete();

        return response()->noContent();
    }
}
