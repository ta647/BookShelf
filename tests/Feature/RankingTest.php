<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    // 正常系
    public function test_ログイン済みユーザーがランキング画面を開くことができる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');
    }

    public function test_未ログインユーザーがランキング画面を開くことができる(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');
    }

    public function test_書籍タイトルを押すと書籍詳細画面に遷移する(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'ランキング書籍']);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('ranking.index'));

        $response->assertSee(route('books.show', $book), false);
    }

    public function test_ランキング画面にレビュー平均評価の書籍が降順で表示される(): void
    {
        $user = User::factory()->create();
        $topBook = Book::factory()->create(['title' => '高評価書籍']);
        $lowBook = Book::factory()->create(['title' => '低評価書籍']);

        Review::factory()->create([
            'book_id' => $topBook->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'book_id' => $lowBook->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 1,
        ]);

        $response = $this->get(route('ranking.index'));
        $content = $response->getContent();

        $this->assertLessThan(
            strpos($content, '低評価書籍'),
            strpos($content, '高評価書籍')
        );
    }

    public function test_レビューがない書籍はランキングに表示されない(): void
    {
        Book::factory()->create(['title' => 'レビューなし書籍']);

        $response = $this->get(route('ranking.index'));

        $response->assertDontSee('レビューなし書籍');
    }
}
