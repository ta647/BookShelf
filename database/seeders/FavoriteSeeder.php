<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();

        $favorites = [
            // 山田太郎: 5冊
            ['user' => 1, 'books' => [1, 3, 6, 8, 10]],
            // 鈴木花子: 4冊
            ['user' => 2, 'books' => [2, 4, 7, 9]],
            // 田中一郎: 3冊
            ['user' => 3, 'books' => [3, 6, 11]],
            // 佐藤美咲: 5冊
            ['user' => 4, 'books' => [1, 4, 8, 9, 10]],
            // 高橋健太: 4冊
            ['user' => 5, 'books' => [2, 5, 7, 11]],
        ];

        $users = User::all();

        foreach ($favorites as $data) {
            $user = $users[$data['user'] - 1];
            foreach ($data['books'] as $bookIndex) {
                $book = $books[$bookIndex - 1];
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                ]);
            }
        }
    }
}
