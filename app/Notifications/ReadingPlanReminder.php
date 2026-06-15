<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly ReadingPlan $plan)
    {
    }

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
            'book_title'      => $this->plan->book->title,
            'due_date'        => $this->plan->due_date->toDateString(),
            'message'         => "「{$this->plan->book->title}」の読書期日が明日（{$this->plan->due_date->toDateString()}）です。",
        ];
    }
}
