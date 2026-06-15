<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BatchProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_期日を過ぎた読書計画が期限切れに更新される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 期日が昨日の進行中計画
        $overdueplan = ReadingPlan::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'status'   => ReadingPlanStatus::Reading,
            'due_date' => Carbon::yesterday(),
        ]);

        // 期日が今日の進行中計画（まだ期限切れにしてはいけない）
        $todayPlan = ReadingPlan::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => Book::factory()->create()->id,
            'status'   => ReadingPlanStatus::Reading,
            'due_date' => Carbon::today(),
        ]);

        $this->artisan('app:expire-reading-plans')->assertExitCode(0);

        $this->assertEquals(ReadingPlanStatus::Expired, $overdueplan->fresh()->status);
        $this->assertEquals(ReadingPlanStatus::Reading, $todayPlan->fresh()->status);
    }

    public function test_完了済み計画は期限切れに変更されない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $completedPlan = ReadingPlan::factory()->create([
            'user_id'      => $user->id,
            'book_id'      => $book->id,
            'status'       => ReadingPlanStatus::Completed,
            'due_date'     => Carbon::yesterday(),
            'completed_at' => Carbon::yesterday(),
        ]);

        $this->artisan('app:expire-reading-plans')->assertExitCode(0);

        $this->assertEquals(ReadingPlanStatus::Completed, $completedPlan->fresh()->status);
    }

    public function test_明日が期日の読書計画でリマインダー通知が送られる(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'status'   => ReadingPlanStatus::Reading,
            'due_date' => Carbon::tomorrow(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            \App\Notifications\ReadingPlanReminder::class
        );
    }

    public function test_期日が明日でない計画にはリマインダーが送られない(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 期日が3日後
        ReadingPlan::factory()->create([
            'user_id'  => $user->id,
            'book_id'  => $book->id,
            'status'   => ReadingPlanStatus::Reading,
            'due_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_完了済み計画にはリマインダーが送られない(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id'      => $user->id,
            'book_id'      => $book->id,
            'status'       => ReadingPlanStatus::Completed,
            'due_date'     => Carbon::tomorrow(),
            'completed_at' => Carbon::today(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
