<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookIds = Book::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        $reviewCounts = [2,4,3,2,3,4,3,4,2,3,2];

        foreach ($bookIds as $key => $bookId) {
            $reviewCount = $reviewCounts[$key];

            $selectedUsers = collect($userIds)
                ->shuffle()
                ->take($reviewCount);

            foreach ($selectedUsers as $userId) {
                Review::factory()->create([
                    'book_id' => $bookId,
                    'user_id' => $userId,
                ]);
            }
        }
    }
}
