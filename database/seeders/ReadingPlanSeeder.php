<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->firstOrFail();
        $suzuki = User::where('email', 'suzuki@example.com')->firstOrFail();

        $books = Book::orderBy('id')->get();

        $today = Carbon::today();

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'target_date' => $today->copy()->addDays(10),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'target_date' => $today->copy()->addDays(3),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'target_date' => $today->copy(),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'target_date' => $today->copy()->subDays(1),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Reading->value,
        ]);

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'target_date' => $today->copy()->subDays(5),
            'completed_at' => $today->copy()->subDays(5),
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[5]->id,
            'target_date' => $today->copy()->subDays(2),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[6]->id,
            'target_date' => $today->copy()->addDays(10),
            'completed_at' => null,
            'status' => ReadingPlanStatus::Reading->value,
        ]);
    }
}
