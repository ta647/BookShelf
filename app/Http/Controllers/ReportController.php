<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポートを表示する
     */
    public function index(): View
    {
        $user    = Auth::user();
        $reviews = $user->reviews()->with('book.genres')->get();

        $summary = [
            'total_reviews'  => $reviews->count(),
            'books_read'     => $reviews->pluck('book_id')->unique()->count(),
            'average_rating' => $reviews->avg('rating') ?? 0,
        ];

        // 評価分布（1〜5星それぞれの件数）
        $ratingDistribution = collect(range(1, 5))->map(
            fn (int $star) => $reviews->where('rating', $star)->count()
        );

        // 高評価書籍TOP5（4星以上、評価降順）
        $topRatedBooks = $reviews->filter(fn ($r) => $r->rating >= 4)
            ->sortByDesc('rating')
            ->unique('book_id')
            ->take(5)
            ->map(fn ($r) => [
                'id'     => $r->book->id,
                'title'  => $r->book->title,
                'author' => $r->book->author,
                'rating' => $r->rating,
            ])
            ->values();

        // ジャンル別平均評価TOP5
        $genreRatings = $reviews->flatMap(function ($review) {
            return $review->book->genres->map(fn ($genre) => [
                'genre_id'   => $genre->id,
                'genre_name' => $genre->name,
                'rating'     => $review->rating,
            ]);
        })
        ->groupBy('genre_id')
        ->map(fn ($items, $genreId) => [
            'id'             => $genreId,
            'name'           => $items->first()['genre_name'],
            'average_rating' => round($items->avg('rating'), 1),
            'count'          => $items->count(),
        ])
        ->sortByDesc('average_rating')
        ->take(5)
        ->values();

        $stats = [
            'summary'             => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books'     => $topRatedBooks,
            'genre_ratings'       => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
