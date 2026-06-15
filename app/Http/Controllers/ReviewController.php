<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを投稿する
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集フォームを表示する
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update([
            'rating'  => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;
        $review->reviewLikes()->delete();
        $review->delete();

        return redirect()->route('books.show', $book)->with('success', 'レビューを削除しました。');
    }

    /**
     * レビューへのいいねを追加/解除する
     */
    public function like(Review $review): RedirectResponse
    {
        $userId = auth()->id();
        $existing = ReviewLike::where('user_id', $userId)->where('review_id', $review->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            ReviewLike::create(['user_id' => $userId, 'review_id' => $review->id]);
        }

        return redirect()->route('books.show', $review->book);
    }
}
