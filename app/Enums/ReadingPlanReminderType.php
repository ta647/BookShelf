<?php

namespace App\Enums;

enum ReadingPlanReminderType: string
{
    case Advance = 'three_days_before';
    case DueDate = 'on_due_date';
    case Reengagement = 'three_days_after';

    public function label(): string
    {
        return match ($this) {
            self::Advance => '予告通知',
            self::DueDate => '最終リマインド通知',
            self::Reengagement => '再エンゲージメント通知',
        };
    }
}
