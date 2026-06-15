<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookIndexRequest;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する（検索・絞り込み・ページネーション対応）
     */
    public function index(BookIndexRequest $request): AnonymousResourceCollection
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
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $request->genre));
        }

        $books = $query->latest()->paginate($request->per_page ?? 10);

        return BookResource::collection($books);
    }

    /**
     * 書籍詳細を取得する
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load(['genres', 'reviews.user']);

        return new BookDetailResource($book);
    }

    /**
     * 書籍を登録する（Sanctum認証必須）
     */
    public function store(StoreBookRequest $request): JsonResponse
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

        return (new BookDetailResource($book->load('genres')))->response()->setStatusCode(201);
    }

    /**
     * 書籍を更新する（Sanctum認証 + 所有者のみ）
     */
    public function update(UpdateBookRequest $request, Book $book): BookDetailResource
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

        return new BookDetailResource($book->load('genres'));
    }

    /**
     * 書籍を削除する（Sanctum認証 + 所有者のみ）
     */
    public function destroy(Book $book): \Illuminate\Http\Response
    {
        $this->authorize('delete', $book);

        $book->reviews->each(fn ($review) => $review->reviewLikes()->delete());
        $book->reviews()->delete();
        $book->favorites()->delete();
        $book->genres()->detach();
        $book->delete();

        return response()->noContent();
    }
}
