<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り書籍一覧を表示する
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->with('genres')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入りを追加/解除する
     */
    public function toggle(Book $book): RedirectResponse
    {
        $existing = Favorite::where('user_id', auth()->id())->where('book_id', $book->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            Favorite::create(['user_id' => auth()->id(), 'book_id' => $book->id]);
        }

        return redirect()->route('books.show', $book);
    }
}
