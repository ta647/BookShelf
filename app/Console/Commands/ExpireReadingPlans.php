<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireReadingPlans extends Command
{
    protected $signature = 'app:expire-reading-plans';

    protected $description = '期日を過ぎた読書計画のステータスを期限切れに変更する';

    public function handle(): int
    {
        $count = ReadingPlan::where('status', ReadingPlanStatus::Reading)
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => ReadingPlanStatus::Expired]);

        $this->info("期限切れにした計画: {$count}件");

        return self::SUCCESS;
    }
}
