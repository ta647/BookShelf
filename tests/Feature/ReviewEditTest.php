<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Review $review;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $book = Book::factory()->create();
        $this->review = Review::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);
    }

    // 正常系
    public function test_作成者本人がレビュー編集画面を開くと既存データが表示される(): void
    {
        $response = $this->actingAs($this->user)->get(route('reviews.edit', $this->review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
        $response->assertSee('元のコメント');
    }

    public function test_作成者本人がレビュー内容を編集して更新できる(): void
    {
        $this->actingAs($this->user)->put(route('reviews.update', $this->review), [
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);
    }

    // 異常系
    public function test_評価を未入力で更新するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->put(route('reviews.update', $this->review), [
            'rating' => '',
            'comment' => 'コメント',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }
}
