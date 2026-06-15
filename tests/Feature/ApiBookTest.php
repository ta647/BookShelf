<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
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

    // ─── 認証不要エンドポイント ───────────────────────────────────

    public function test_書籍一覧をAPIで取得できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_書籍詳細をAPIで取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $book->id);
    }

    public function test_存在しない書籍IDでは404になる(): void
    {
        $response = $this->getJson('/api/v1/books/9999');

        $response->assertNotFound();
    }

    // ─── Sanctum認証が必要なエンドポイント ───────────────────────

    public function test_未認証では書籍を登録できない(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title'  => 'テスト本',
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertUnauthorized();
    }

    public function test_Sanctum認証で書籍を登録できる(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/books', [
            'title'       => 'テスト本',
            'author'      => '著者名',
            'description' => '説明文',
            'genres'      => [$this->genre->id],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'テスト本');

        $this->assertDatabaseHas('books', [
            'title'   => 'テスト本',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_書籍登録時にタイトルは必須(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/books', [
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_書籍登録時にジャンルは必須(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/books', [
            'title'  => 'テスト本',
            'author' => '著者名',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['genres']);
    }

    public function test_未認証では書籍を更新できない(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title'  => '更新後タイトル',
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertUnauthorized();
    }

    public function test_Sanctum認証で書籍を更新できる(): void
    {
        $book  = Book::factory()->create(['user_id' => $this->user->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $book->genres()->attach($this->genre);

        $response = $this->withToken($token)->putJson("/api/v1/books/{$book->id}", [
            'title'  => '更新後タイトル',
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', '更新後タイトル');
    }

    public function test_他人の書籍は更新できない(): void
    {
        $otherUser = User::factory()->create();
        $book      = Book::factory()->create(['user_id' => $otherUser->id]);
        $token     = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->putJson("/api/v1/books/{$book->id}", [
            'title'  => '更新後タイトル',
            'author' => '著者名',
            'genres' => [$this->genre->id],
        ]);

        $response->assertForbidden();
    }

    public function test_未認証では書籍を削除できない(): void
    {
        $book = Book::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertUnauthorized();
    }

    public function test_Sanctum認証で書籍を削除できる(): void
    {
        $book  = Book::factory()->create(['user_id' => $this->user->id]);
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_他人の書籍は削除できない(): void
    {
        $otherUser = User::factory()->create();
        $book      = Book::factory()->create(['user_id' => $otherUser->id]);
        $token     = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_APIでキーワード検索できる(): void
    {
        Book::factory()->create(['title' => 'Laravel実践']);
        Book::factory()->create(['title' => 'Python入門']);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Laravel実践', $data[0]['title']);
    }

    public function test_APIでジャンルフィルタができる(): void
    {
        $genre2 = Genre::factory()->create();
        $book1  = Book::factory()->create(['title' => 'Genre1の本']);
        $book2  = Book::factory()->create(['title' => 'Genre2の本']);

        $book1->genres()->attach($this->genre);
        $book2->genres()->attach($genre2);

        $response = $this->getJson("/api/v1/books?genre={$this->genre->id}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Genre1の本', $data[0]['title']);
    }
}
