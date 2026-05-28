<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {
            $otherUsers = $users->where('id', '!=', $review->user_id)->values();
            $likeCount = rand(0, min(3, $otherUsers->count()));
            $likers = $otherUsers->shuffle()->take($likeCount);

            foreach ($likers as $user) {
                ReviewLike::firstOrCreate([
                    'user_id' => $user->id,
                    'review_id' => $review->id,
                ]);
            }
        }
    }
}
