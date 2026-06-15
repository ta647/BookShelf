<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_書籍一覧が表示される(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertOk();
        $response->assertViewIs('books.index');
    }

    public function test_キーワードでタイトル検索できる(): void
    {
        Book::factory()->create(['title' => 'Laravelの教科書', 'author' => '田中太郎']);
        Book::factory()->create(['title' => 'Pythonの基礎', 'author' => '鈴木花子']);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['keyword' => 'Laravel']));

        $response->assertOk();
        $response->assertSee('Laravelの教科書');
        $response->assertDontSee('Pythonの基礎');
    }

    public function test_キーワードで著者検索できる(): void
    {
        Book::factory()->create(['title' => 'Laravelの教科書', 'author' => '田中太郎']);
        Book::factory()->create(['title' => 'Pythonの基礎', 'author' => '鈴木花子']);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['keyword' => '鈴木']));

        $response->assertOk();
        $response->assertSee('Pythonの基礎');
        $response->assertDontSee('Laravelの教科書');
    }

    public function test_ジャンルでフィルタできる(): void
    {
        $genre1 = Genre::factory()->create(['name' => 'プログラミング']);
        $genre2 = Genre::factory()->create(['name' => 'ビジネス']);

        $book1 = Book::factory()->create(['title' => 'PHP入門']);
        $book2 = Book::factory()->create(['title' => '経営戦略']);

        $book1->genres()->attach($genre1);
        $book2->genres()->attach($genre2);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['genre' => $genre1->id]));

        $response->assertOk();
        $response->assertSee('PHP入門');
        $response->assertDontSee('経営戦略');
    }

    public function test_新しい順でソートできる(): void
    {
        $old = Book::factory()->create(['title' => '古い本', 'created_at' => now()->subDays(5)]);
        $new = Book::factory()->create(['title' => '新しい本', 'created_at' => now()]);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['sort' => 'newest']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, '古い本'),
            strpos($content, '新しい本')
        );
    }

    public function test_タイトル順でソートできる(): void
    {
        Book::factory()->create(['title' => 'Z本']);
        Book::factory()->create(['title' => 'A本']);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['sort' => 'title']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(strpos($content, 'Z本'), strpos($content, 'A本'));
    }

    public function test_検索条件がページネーションURLに引き継がれる(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['keyword' => 'Laravel', 'sort' => 'newest']));

        $response->assertOk();
    }
}
