<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_有効なISBNで書籍情報を取得できる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [[
                    'volumeInfo' => [
                        'title'         => 'Laravel実践入門',
                        'authors'       => ['山田太郎'],
                        'publishedDate' => '2023-01-01',
                        'description'   => 'Laravelの解説書',
                        'imageLinks'    => ['thumbnail' => 'https://example.com/cover.jpg'],
                    ],
                ]],
            ]),
        ]);

        $response = $this->actingAs($this->user)->getJson('/books/isbn/9784297127466');

        $response->assertOk()
            ->assertJson([
                'title'          => 'Laravel実践入門',
                'author'         => '山田太郎',
                'published_date' => '2023-01-01',
                'description'    => 'Laravelの解説書',
                'image_url'      => 'https://example.com/cover.jpg',
            ]);
    }

    public function test_13桁でないISBNはバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->getJson('/books/isbn/123');

        $response->assertStatus(422)
            ->assertJsonFragment(['error' => 'ISBNは13桁の数字で入力してください。']);
    }

    public function test_存在しないISBNは404になる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
                'items'      => [],
            ]),
        ]);

        $response = $this->actingAs($this->user)->getJson('/books/isbn/9999999999999');

        $response->assertStatus(404)
            ->assertJsonFragment(['error' => '書籍が見つかりませんでした。']);
    }

    public function test_Google_Books_APIがエラーを返した場合は404になる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($this->user)->getJson('/books/isbn/9784297127466');

        $response->assertStatus(404);
    }
}
