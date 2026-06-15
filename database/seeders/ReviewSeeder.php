<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $commentsByRating = [
            1 => '期待していたほどではありませんでした。内容が薄く感じました。',
            2 => '一部は参考になりましたが、全体的には物足りない印象です。',
            3 => '普通の内容でした。読んで損はありませんが、特に印象には残りません。',
            4 => 'とても良かったです。実践的な内容で参考になりました。',
            5 => '素晴らしい一冊です。多くの人に読んでほしいと思います。',
        ];

        foreach ($books as $book) {
            $reviewCount = rand(2, 4);
            $usedUserIds = [];

            for ($i = 0; $i < $reviewCount; $i++) {
                $available = $users->reject(fn ($u) => in_array($u->id, $usedUserIds));
                if ($available->isEmpty()) {
                    break;
                }

                $user = $available->random();
                $usedUserIds[] = $user->id;
                $rating = rand(1, 5);

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $commentsByRating[$rating],
                ]);
            }
        }
    }
}
