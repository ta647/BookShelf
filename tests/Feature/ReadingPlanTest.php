<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    public function test_読書計画一覧が表示される(): void
    {
        ReadingPlan::factory()->reading()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.index');
    }

    public function test_ステータスで絞り込める(): void
    {
        ReadingPlan::factory()->reading()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
        ReadingPlan::factory()->completed()->create([
            'user_id' => $this->user->id,
            'book_id' => Book::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reading-plans.index', ['status' => 'reading']));

        $response->assertOk();
        $plans = $response->viewData('readingPlans');
        $this->assertTrue($plans->every(fn ($p) => $p->status === ReadingPlanStatus::Reading));
    }

    public function test_読書計画作成フォームが表示される(): void
    {
        $response = $this->actingAs($this->user)->get(route('reading-plans.create'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.create');
    }

    public function test_読書計画を登録できる(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id'     => $this->book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'status'  => ReadingPlanStatus::Reading->value,
        ]);
    }

    public function test_過去の期日では登録できない(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'book_id'     => $this->book->id,
            'target_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('target_date');
    }

    public function test_書籍未選択では登録できない(): void
    {
        $response = $this->actingAs($this->user)->post(route('reading-plans.store'), [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    public function test_読書計画の期日を更新できる(): void
    {
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $newDate = now()->addDays(14)->format('Y-m-d');

        $response = $this->actingAs($this->user)->put(route('reading-plans.update', $plan), [
            'target_date' => $newDate,
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertEquals($newDate, $plan->fresh()->due_date->format('Y-m-d'));
    }

    public function test_他人の読書計画は更新できない(): void
    {
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('reading-plans.update', $plan), [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertForbidden();
    }

    public function test_読書計画を削除できる(): void
    {
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('reading-plans.destroy', $plan));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    public function test_他人の読書計画は削除できない(): void
    {
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('reading-plans.destroy', $plan));

        $response->assertForbidden();
        $this->assertDatabaseHas('reading_plans', ['id' => $plan->id]);
    }

    public function test_読書計画を読了にできる(): void
    {
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id'     => $plan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
        $this->assertNotNull($plan->fresh()->completed_at);
    }

    public function test_他人の読書計画は読了にできない(): void
    {
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->reading()->create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('reading-plans.complete', $plan));

        $response->assertForbidden();
    }

    public function test_未認証では一覧にアクセスできない(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }
}
