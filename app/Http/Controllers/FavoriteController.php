<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = auth()->user()->favoriteBooks()->with('genres')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
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
