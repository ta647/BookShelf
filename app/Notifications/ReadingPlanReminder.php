<?php

namespace App\Notifications;

use App\Enums\ReadingPlanReminderType;
use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReadingPlan $plan,
        private readonly ReadingPlanReminderType $type,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reading_plan_id' => $this->plan->id,
            'book_title' => $this->plan->book->title,
            'due_date' => $this->plan->due_date->toDateString(),
            'timing' => $this->type->value,
            'title' => $this->type->label(),
            'body' => $this->buildMessage(),
        ];
    }

    /**
     * 通知タイプに応じたメッセージを組み立てる
     */
    private function buildMessage(): string
    {
        $bookTitle = $this->plan->book->title;
        $dueDate = $this->plan->due_date->toDateString();

        return match ($this->type) {
            ReadingPlanReminderType::Advance => "「{$bookTitle}」の読書期日（{$dueDate}）まであと3日です。",
            ReadingPlanReminderType::DueDate => "「{$bookTitle}」の読書期日は本日（{$dueDate}）です。",
            ReadingPlanReminderType::Reengagement => "「{$bookTitle}」の読書期日（{$dueDate}）を過ぎています。計画を見直してみませんか？",
        };
    }
}
