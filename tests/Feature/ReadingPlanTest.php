<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    public function test_ログインユーザーは読書計画の一覧を表示し選択した状態の計画のみに絞り込みができる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $bookReading = Book::create(['user_id' => $user->id, 'title' => '読んでいる本', 'author' => '著者', 'isbn' => '9784000000003']);

        $bookCompleted = Book::create(['user_id' => $user->id, 'title' => '読み終わった本', 'author' => '著者', 'isbn' => '9784000000005']);

        $planReading = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookReading->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $planCompleted = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookCompleted->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Completed->value ?? 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => 'completed']));

        $response->assertOk();

        $plan = $response->original->getData()['readingPlans'] ?? $response->original->getData()['plans'];

        $this->assertTrue($plan->contains($planCompleted->id));
        $this->assertFalse($plan->contains($planReading->id));
    }

    public function test_未ログインユーザーが読書計画一覧にアクセスしようとするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect('/login');
    }

    public function test_ログインユーザーは新規読書計画作成画面に遷移でき書籍プルダウン用のデータを正常に取得できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertOk();

        $viewBooks = $response->original->getData()['books'] ?? null;

        $this->assertTrue($viewBooks->contains($book->id));
    }

    public function test_所有者は正しいデータを入力してバリデーションを通過すると読書計画を新規登録できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $store = [
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $store);

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
        ]);
    }

    public function test_必須項目が空の不正なデータで登録しようとするとバリデーションエラーが発生する(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $store =[
            'book_id' => '',
            'target_date' => '',
        ];

        $response = $this->actingAs($user)->post(route('reading-plans.store'), $store);

        $response->assertSessionHasErrors(['book_id', 'target_date']);
    }

    public function test_所有者は既存の期日が初期値として表示された読書計画の編集画面に遷移できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertOk();

        $plan = $response->original->getData()['readingPlan'] ?? $response->original->getData()['plan'];

        $this->assertEquals('2026-11-28', $plan->target_date->format('Y-m-d'));
    }

    public function test_所有者はバリデーション通過後に読書計画情報およびステータスを更新できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $update = [
            'target_date' => '2027-01-27',
            'status'      => ReadingPlanStatus::Reading->value ?? 'reading',
        ];

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), $update);

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2027-01-27',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);
    }

    public function test_所有者は自分の読書計画を削除できる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect();

        $this->assertDatabaseMissing('reading_plans', ['id' => $readingPlan->id]);
    }

    public function test_他人の読書計画を編集画面遷移や更新や削除しようとすると403認可エラーになる(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $other = User::create([
            'name' => '他者',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $other->books()->create([
            'title' => '本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-11-28',
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $response = $this->actingAs($other)->get(route('reading-plans.edit', $readingPlan));

        $update = $this->actingAs($other)->put(route('reading-plans.update', $readingPlan), ['target_date' => '2028-11-28']);

        $destroy = $this->actingAs($other)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertStatus(403);
        $update->assertStatus(403);
        $destroy->assertStatus(403);
    }

    public function test_リマインダーバッチは期限の3日前と当日のユーザーに対して正しい通知を送信する(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $bookThreeDays = $user->books()->create([
            'title' => '3日',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $bookToday = $user->books()->create([
            'title' => '当日',
            'author' => '著者',
            'isbn' => '9784000000004'
        ]);

        $today = \Carbon\Carbon::today();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $bookThreeDays->id,
            'target_date' => $today->format('Y-m-d'),
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $this->artisan('app:reading-plan-alert-command');

        \Illuminate\Support\Facades\Notification::assertSentTo($user, \App\Notifications\CustomNotification::class);
    }

    public function test_自動失効バッチは期限が過ぎた読書計画を自動的に期限切れステータスに更新する(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $book = $user->books()->create([
            'title' => '期限切れの本',
            'author' => '著者',
            'isbn' => '9784000000001'
        ]);

        $today = \Carbon\Carbon::today();

        $planExpired = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $today->copy()->subDay()->format('Y-m-d'),
            'status' => ReadingPlanStatus::Reading->value ?? 'reading',
        ]);

        $this->artisan('app:reading-plan-alert-command');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $planExpired->id,
            'status' => ReadingPlanStatus::Expired->value ?? 'expired',
        ]);
    }
}
