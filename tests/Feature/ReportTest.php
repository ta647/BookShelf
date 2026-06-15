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
        $user  = User::factory()->create();
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
        $user  = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'プログラミング']);
        $book  = Book::factory()->create();
        $book->genres()->attach($genre);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $genreRatings = $stats['genre_ratings'];

        $this->assertNotEmpty($genreRatings);
        $this->assertEquals('プログラミング', $genreRatings->first()['name']);
    }

    public function test_未認証ではレポートにアクセスできない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
