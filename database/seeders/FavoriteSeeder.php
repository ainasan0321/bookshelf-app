<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $user) {
            #3〜5冊分の件数を決める
            $count = rand(3, 5);

            #その件数だけ本を選ぶ
            $selectedBooks = $books->random($count);

            $bookIds = $selectedBooks->pluck('id')->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
