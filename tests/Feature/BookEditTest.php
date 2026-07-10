<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->genre = Genre::factory()->create();
        $this->book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title' => '編集前タイトル',
        ]);
        $this->book->genres()->attach($this->genre);
    }

    // 正常系
    public function test_作成者本人が書籍編集画面を開くと既存データが表示される(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.edit', $this->book));

        $response->assertOk();
        $response->assertViewIs('books.edit');
        $response->assertSee('編集前タイトル');
    }

    public function test_編集画面のviewに書籍とジャンル一覧が渡される(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.edit', $this->book));

        $response->assertViewHas('book', fn (Book $book) => $book->is($this->book));
        $response->assertViewHas('genres');
    }

    public function test_作成者本人が内容を変更すると書籍とジャンル紐付けが更新される(): void
    {
        $newGenre = Genre::factory()->create();

        $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$newGenre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $this->book->id,
            'genre_id' => $newGenre->id,
        ]);
    }

    // 権限系
    public function test_作成者でないユーザーは編集画面を表示できない(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->get(route('books.edit', $this->book));

        $response->assertForbidden();
    }

    public function test_作成者でないユーザーは更新できない(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('books.update', $this->book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'genres' => [$this->genre->id],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('books', [
            'id' => $this->book->id,
            'title' => '編集前タイトル',
        ]);
    }

    // 異常系
    public function test_タイトルを空にして更新するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => '',
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_著者を空にして更新するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => 'タイトル',
            'author' => '',
            'genres' => [$this->genre->id],
        ]);

        $response->assertSessionHasErrors(['author']);
    }

    public function test_ジャンルを選択せずに更新するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->put(route('books.update', $this->book), [
            'title' => 'タイトル',
            'author' => '著者名',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors(['genres']);
    }
}
