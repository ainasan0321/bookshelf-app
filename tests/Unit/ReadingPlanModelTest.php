<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanModelTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_期日が自動で_carbonオブジェクトに変換される(): void
    {
        // 準備
        $plan = new ReadingPlan;

        // 実行
        $plan->target_date = '2026-08-31';

        // 検証
        $this->assertInstanceOf(Carbon::class, $plan->target_date);
        $this->assertEquals('2026-08-31', $plan->target_date->format('Y-m-d'));
    }

    public function test_正しいリレーションを返す(): void
    {
        // 準備
        $plan = new ReadingPlan;

        // 実行
        $relation = $plan->user();

        // 検証
        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    public function test_指定した項目が一括で安全にセットできる(): void
    {
        // 準備
        $plan = new ReadingPlan([
            'user_id' => 1,
            'book_id' => 2,
            'target_date' => '2026-08-31',
            'status' => ReadingPlanStatus::Reading,
        ]);

        // 検証
        $this->assertEquals('1', $plan->user_id);
        $this->assertEquals('2', $plan->book_id);
        $this->assertEquals(ReadingPlanStatus::Reading, $plan->status);
    }
}
