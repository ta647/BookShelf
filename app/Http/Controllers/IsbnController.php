<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class IsbnController extends Controller
{
    /**
     * ISBNでGoogle Books APIから書籍情報を取得する
     */
    public function search(string $isbn): JsonResponse
    {
        if (strlen($isbn) !== 13) {
            return response()->json(['error' => 'ISBNは13桁で入力してください。'], 400);
        }

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q'   => "isbn:{$isbn}",
            'key' => config('services.google.books_api_key'),
        ]);

        if ($response->status() === 429) {
            return response()->json(['error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。'], 429);
        }

        if (!$response->successful()) {
            return response()->json(['error' => 'API通信エラーが発生しました。'], 500);
        }

        if ($response->json('totalItems', 0) === 0) {
            return response()->json(['error' => '書籍が見つかりませんでした。'], 404);
        }

        $item       = $response->json('items.0.volumeInfo');
        $imageLinks = $item['imageLinks'] ?? [];

        return response()->json([
            'title'          => $item['title'] ?? '',
            'author'         => implode(', ', $item['authors'] ?? []),
            'published_date' => $item['publishedDate'] ?? '',
            'description'    => $item['description'] ?? '',
            'image_url'      => $imageLinks['thumbnail'] ?? $imageLinks['smallThumbnail'] ?? '',
        ]);
    }
}
