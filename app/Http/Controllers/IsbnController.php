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
        if (!preg_match('/^\d{13}$/', $isbn)) {
            return response()->json(['error' => 'ISBNは13桁の数字で入力してください。'], 422);
        }

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => "isbn:{$isbn}",
        ]);

        if (!$response->successful() || ($response->json('totalItems', 0) === 0)) {
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
