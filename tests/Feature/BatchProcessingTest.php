<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanReminderType;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
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
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Reading,
            'due_date' => Carbon::yesterday(),
        ]);

        // 期日が今日の進行中計画（まだ期限切れにしてはいけない）
        $todayPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'status' => ReadingPlanStatus::Reading,
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
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Completed,
            'due_date' => Carbon::yesterday(),
            'completed_at' => Carbon::yesterday(),
        ]);

        $this->artisan('app:expire-reading-plans')->assertExitCode(0);

        $this->assertEquals(ReadingPlanStatus::Completed, $completedPlan->fresh()->status);
    }

    public function test_期日3日前の進行中計画に予告通知が送られる(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Reading,
            'due_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminder::class,
            fn (ReadingPlanReminder $notification) => $notification->toArray($user)['timing'] === ReadingPlanReminderType::Advance->value
        );
    }

    public function test_期日当日の進行中計画に最終リマインド通知が送られる(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Reading,
            'due_date' => Carbon::today(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminder::class,
            fn (ReadingPlanReminder $notification) => $notification->toArray($user)['timing'] === ReadingPlanReminderType::DueDate->value
        );
    }

    public function test_期日3日後の期限切れ計画に再エンゲージメント通知が送られる(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Expired,
            'due_date' => Carbon::today()->subDays(3),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminder::class,
            fn (ReadingPlanReminder $notification) => $notification->toArray($user)['timing'] === ReadingPlanReminderType::Reengagement->value
        );
    }

    public function test_発火タイミング以外の期日にはリマインダーが送られない(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        // 期日が明日の進行中計画（3日前・当日のいずれにも該当しない）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'status' => ReadingPlanStatus::Reading,
            'due_date' => Carbon::tomorrow(),
        ]);

        // 期日が昨日の期限切れ計画（3日後に該当しない）
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create()->id,
            'status' => ReadingPlanStatus::Expired,
            'due_date' => Carbon::yesterday(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_期日3日後でも進行中の計画には再エンゲージメント通知が送られない(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Reading,
            'due_date' => Carbon::today()->subDays(3),
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
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Completed,
            'due_date' => Carbon::today(),
            'completed_at' => Carbon::today(),
        ]);

        $this->artisan('app:send-reading-plan-reminders')->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
