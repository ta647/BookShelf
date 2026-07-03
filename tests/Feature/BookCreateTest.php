<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCreateTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = User::factory()->create();
        $this->genre = Genre::factory()->create();
    }

    // 正常系
    public function test_書籍登録フォームが表示される(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.create'));

        $response->assertOk();
        $response->assertViewIs('books.create');
    }

    public function test_必須項目を全て入力すると書籍が登録され詳細ページにリダイレクトされる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => 'テスト書籍',
            'author' => 'テスト著者',
            'genres' => [$this->genre->id],
        ]);

        $book = Book::where('title', 'テスト書籍')->firstOrFail();
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['title' => 'テスト書籍', 'author' => 'テスト著者']);
    }

    public function test_13桁のISBNを入力すると書籍が登録される(): void
    {
        $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn'   => '9784111111111',
            'genres' => [$this->genre->id],
        ]);

        $this->assertDatabaseHas('books', ['isbn' => '9784111111111']);
    }

    // 異常系
    public function test_タイトルを空にして登録するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => '',
            'author' => 'テスト著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_著者を空にして登録するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => 'テスト書籍',
            'author' => '',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['author']);
    }

    public function test_ジャンルを選択せずに登録するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => 'テスト書籍',
            'author' => 'テスト著者',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors(['genres']);
    }

    public function test_13桁以外のISBNを入力するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn'   => '12345',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['isbn']);
    }

    public function test_255文字を超えるタイトルを入力するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('books.store'), [
            'title'  => str_repeat('あ', 256),
            'author' => 'テスト著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    // 権限系
    public function test_未ログインユーザーが書籍登録画面を開くとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }
}
