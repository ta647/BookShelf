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

    public function test_検索条件がページネーション_ur_lに引き継がれる(): void
    {
        Book::factory()->count(15)->create(['title' => 'Laravel入門']);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['keyword' => 'Laravel', 'sort' => 'newest']));

        $response->assertOk();
        $response->assertSee(
            e(route('books.index', ['keyword' => 'Laravel', 'sort' => 'newest', 'page' => 2])),
            false
        );
    }

    public function test_未ログインユーザーも書籍一覧にアクセスできる(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
        $response->assertViewIs('books.index');
    }

    public function test_書籍タイトルを押すと書籍詳細画面に遷移する(): void
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertSee(route('books.show', $book), false);
    }

    public function test_ログイン済みユーザーが書籍を登録ボタンを押すと書籍登録画面に遷移する(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.index'));

        $response->assertSee(route('books.create'), false);
    }

    public function test_評価順でソートできる(): void
    {
        $reviewer = User::factory()->create();
        $highBook = Book::factory()->create(['title' => '高評価本']);
        $lowBook = Book::factory()->create(['title' => '低評価本']);

        Review::factory()->create([
            'book_id' => $highBook->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'book_id' => $lowBook->id,
            'user_id' => $reviewer->id,
            'rating' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('books.index', ['sort' => 'rating']));

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(strpos($content, '低評価本'), strpos($content, '高評価本'));
    }

    public function test_未ログインユーザーが書籍登録画面にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }
}
