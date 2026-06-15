<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_通知一覧が表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
    }

    public function test_通知を既読にできる(): void
    {
        $user = User::factory()->create();

        // DBに通知を直接挿入
        $notification = DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\ReadingPlanReminder',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['message' => 'テスト通知']),
            'read_at'         => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_他人の通知は既読にできない(): void
    {
        $user      = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\ReadingPlanReminder',
            'notifiable_type' => User::class,
            'notifiable_id'   => $otherUser->id,
            'data'            => json_encode(['message' => 'テスト通知']),
            'read_at'         => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification->id));

        $response->assertStatus(404);
    }

    public function test_未認証では通知一覧にアクセスできない(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }
}
