<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_マイ読書レポートが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
        $response->assertViewHas('stats');
    }

    public function test_レビューなしでもレポートが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats');
    }

    public function test_レポートに正しいサマリー統計が含まれる(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 3]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['summary']['total_reviews']);
        $this->assertEquals(2, $stats['summary']['books_read']);
        $this->assertEquals(4.0, $stats['summary']['average_rating']);
    }

    public function test_ジャンル別評価が集計される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'プログラミング']);
        $book = Book::factory()->create();
        $book->genres()->attach($genre);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $genreRatings = $stats['genre_ratings'];

        $this->assertNotEmpty($genreRatings);
        $this->assertEquals('プログラミング', $genreRatings->first()['name']);
    }

    public function test_レビューが0件の場合サマリーが0で表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');

        $this->assertEquals(0, $stats['summary']['total_reviews']);
        $this->assertEquals(0, $stats['summary']['books_read']);
        $this->assertEquals(0, $stats['summary']['average_rating']);
    }

    public function test_レビューが0件の場合各項目にメッセージが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertSee('4星以上の書籍がありません');
        $response->assertSee('ジャンルが設定された書籍のレビューがありません');
    }

    public function test_高評価書籍が6件以上でも5件に絞られる(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(6)->create();

        foreach ($books as $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => 4,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $this->assertCount(5, $stats['top_rated_books']);
    }

    public function test_高評価書籍が0件の場合メッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 3]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $this->assertCount(0, $stats['top_rated_books']);
        $response->assertSee('4星以上の書籍がありません');
    }

    public function test_ジャンルが6件以上でも5件に絞られる(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $genre = Genre::factory()->create();
            $book = Book::factory()->create();
            $book->genres()->attach($genre);
            Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $this->assertCount(5, $stats['genre_ratings']);
    }

    public function test_他ユーザーのレビューは統計に含まれない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $otherUser->id, 'book_id' => $book2->id, 'rating' => 5]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['summary']['total_reviews']);
    }

    public function test_未認証ではレポートにアクセスできない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
