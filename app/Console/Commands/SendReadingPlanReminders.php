<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'app:send-reading-plan-reminders';

    protected $description = '期日が明日の読書計画にリマインダー通知を送る';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow();

        $plans = ReadingPlan::with(['user', 'book'])
            ->where('status', ReadingPlanStatus::Reading)
            ->whereDate('due_date', $tomorrow)
            ->get();

        $plans->each(fn (ReadingPlan $plan) => $plan->user->notify(new ReadingPlanReminder($plan)));

        $this->info("リマインダー送信: {$plans->count()}件");

        return self::SUCCESS;
    }
}
