<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // 正常系
    public function test_お気に入り一覧にログインユーザーのお気に入り書籍が表示される(): void
    {
        $book = Book::factory()->create(['title' => 'お気に入り書籍']);
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertOk();
        $response->assertViewIs('favorites.index');
        $response->assertSee('お気に入り書籍');
    }

    public function test_お気に入り一覧は10件ずつページネーションされる(): void
    {
        $books = Book::factory()->count(12)->create();
        foreach ($books as $book) {
            Favorite::create(['user_id' => $this->user->id, 'book_id' => $book->id]);
        }

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertOk();
    }

    public function test_書籍タイトルを押すと書籍詳細画面に遷移する(): void
    {
        $book = Book::factory()->create();
        Favorite::create(['user_id' => $this->user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($this->user)->get(route('favorites.index'));

        $response->assertSee(route('books.show', $book), false);
    }

    // 権限系
    public function test_未ログインユーザーがお気に入り一覧を開くとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }
}
