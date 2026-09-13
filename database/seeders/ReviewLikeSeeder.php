<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            $otherUsers = $users->reject(
                fn ($user) => $user->id === $review->user_id
            );
            $count = rand(0, 3);

            if ($count === 0) {
                continue;
            }

            $selectedUsers = $otherUsers->random($count);

            $userIds = $selectedUsers->pluck('id')->toArray();

            $review->likedByUsers()->syncWithoutDetaching($userIds);
        }
    }
}
