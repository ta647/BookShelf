<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ジャンル一覧 正常系
    public function test_ジャンル一覧が書籍数付きで表示される(): void
    {
        $genre = Genre::factory()->create(['name' => 'テストジャンル']);
        Book::factory()->create()->genres()->attach($genre);

        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewIs('genres.index');
        $response->assertSee('テストジャンル');
    }

    public function test_ジャンル名を押すとジャンル詳細画面に遷移する(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertSee(route('genres.show', $genre), false);
    }

    public function test_ジャンルを登録ボタンを押すとジャンル登録画面に遷移する(): void
    {
        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertSee(route('genres.create'), false);
    }

    public function test_各ジャンルの編集ボタンを押すとジャンル編集画面に遷移する(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)->get(route('genres.index'));

        $response->assertSee(route('genres.edit', $genre), false);
    }

    public function test_書籍が紐付いていないジャンルは削除できる(): void
    {
        $genre = Genre::factory()->create();

        $this->actingAs($this->user)->delete(route('genres.destroy', $genre));

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_書籍が紐付いているジャンルは削除を制限される(): void
    {
        $genre = Genre::factory()->create();
        $book  = Book::factory()->create();
        $book->genres()->attach($genre);

        $response = $this->actingAs($this->user)->delete(route('genres.destroy', $genre));

        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error');
    }

    // ジャンル一覧 権限系
    public function test_未ログインユーザーがジャンル一覧を開くとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    // ジャンル詳細 正常系
    public function test_ジャンル詳細画面にジャンル名と紐づく書籍がページネーション表示される(): void
    {
        $genre = Genre::factory()->create(['name' => '詳細ジャンル']);
        Book::factory()->count(3)->create()->each(fn ($book) => $book->genres()->attach($genre));

        $response = $this->actingAs($this->user)->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');
        $response->assertSee('詳細ジャンル');
    }

    // ジャンル詳細 権限系
    public function test_未ログインユーザーがジャンル詳細を開くとログイン画面にリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect(route('login'));
    }

    // ジャンル登録 正常系
    public function test_ジャンル登録画面にジャンル名入力フォームが表示される(): void
    {
        $response = $this->actingAs($this->user)->get(route('genres.create'));

        $response->assertOk();
        $response->assertViewIs('genres.create');
    }

    public function test_ジャンル名を入力して登録ボタンを押すとジャンルが登録される(): void
    {
        $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => '新しいジャンル',
        ]);

        $this->assertDatabaseHas('genres', ['name' => '新しいジャンル']);
    }

    // ジャンル登録 異常系
    public function test_ジャンル名を空にして登録するとバリデーションエラーになる(): void
    {
        $response = $this->actingAs($this->user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    // ジャンル登録 権限系
    public function test_未ログインユーザーがジャンル登録画面を開くとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    // ジャンル編集 正常系
    public function test_ジャンル編集画面に現在のジャンル名が初期値として表示される(): void
    {
        $genre = Genre::factory()->create(['name' => '編集前ジャンル']);

        $response = $this->actingAs($this->user)->get(route('genres.edit', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.edit');
        $response->assertSee('編集前ジャンル');
    }

    public function test_ジャンル名を変更して更新ボタンを押すとジャンル名が更新される(): void
    {
        $genre = Genre::factory()->create(['name' => '編集前ジャンル']);

        $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => '編集後ジャンル',
        ]);

        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '編集後ジャンル']);
    }

    // ジャンル編集 異常系
    public function test_ジャンル名を空にして更新するとバリデーションエラーになる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->actingAs($this->user)->put(route('genres.update', $genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    // ジャンル編集 権限系
    public function test_未ログインユーザーがジャンル編集画面を開くとログイン画面にリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertRedirect(route('login'));
    }
}
