<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\CustomNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReadingPlanAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reading-plan-alert-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限チェック、ステータス自動変更、およびリマインダーの送信';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $activePlans = ReadingPlan::where('status', '!=', ReadingPlanStatus::Completed->value)
            ->with(['user', 'book'])
            ->get();

        foreach ($activePlans as $plan) {
            $user = $plan->user;
            $bookTitle = $plan->book->title ?? '登録された本';
            $targetDate = Carbon::parse($plan->target_date);

            if ($targetDate->copy()->subDay(3)->equalTo($today)) {
                $user->notify(new CustomNotification([
                    'title' => '読書リマインダー(あと3日)',
                    'body' => "「{$bookTitle}」の読書期日まであと3日になりました。読み進んでいますか？",
                    'timing' => 'three_days_before',
                ]));

            } elseif ($targetDate->equalTo($today)) {
                $user->notify(new CustomNotification([
                    'title' => '本日が読書期限です！',
                    'body' => "今日が「{$bookTitle}」の読書期限の日だよ！",
                    'timing' => 'on_due_date',
                ]));

            } elseif ($targetDate->lessThan($today)) {
                $plan->update([
                    'status' => ReadingPlanStatus::Expired->value,
                ]);

                $user->notify(new CustomNotification([
                    'title' => '読書期限超過のお知らせ',
                    'body' => "「{$bookTitle}」の読書期限が切れました。",
                    'timing' => 'three_days_after',
                ]));
            }
        }
        $this->info('読書計画の期限チェックバッチが正常に完了しました。');
    }
}
