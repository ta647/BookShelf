<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanReminderType;
use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'app:send-reading-plan-reminders';

    protected $description = '読書計画にリマインダー通知を送る（期日3日前・当日・3日後）';

    /**
     * 次の3タイミングでリマインダー通知を送信する
     * ・期日3日前の「進行中」計画 → 予告通知
     * ・期日当日の「進行中」計画 → 最終リマインド通知
     * ・期日3日後の「期限切れ」計画 → 再エンゲージメント通知
     *
     * @return int コマンドの終了ステータス
     */
    public function handle(): int
    {
        $today = Carbon::today();

        $count = $this->send($today->copy()->addDays(3), ReadingPlanStatus::Reading, ReadingPlanReminderType::Advance)
            + $this->send($today, ReadingPlanStatus::Reading, ReadingPlanReminderType::DueDate)
            + $this->send($today->copy()->subDays(3), ReadingPlanStatus::Expired, ReadingPlanReminderType::Reengagement);

        $this->info("リマインダー送信: {$count}件");

        return self::SUCCESS;
    }

    /**
     * 指定した期日・ステータスに合致する読書計画へ通知を送信する
     *
     * @return int 送信した通知の件数
     */
    private function send(Carbon $dueDate, ReadingPlanStatus $status, ReadingPlanReminderType $type): int
    {
        $plans = ReadingPlan::with(['user', 'book'])
            ->where('status', $status)
            ->whereDate('due_date', $dueDate)
            ->get();

        $plans->each(fn (ReadingPlan $plan) => $plan->user->notify(new ReadingPlanReminder($plan, $type)));

        return $plans->count();
    }
}
