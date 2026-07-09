<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['user_id' => $this->user->id]);
    }

    // 正常系
    public function test_ログイン済みユーザーが書籍詳細画面にアクセスできる(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.show', $this->book));

        $response->assertOk();
        $response->assertViewIs('books.show');
    }

    public function test_未ログインユーザーが書籍詳細画面にアクセスできる(): void
    {
        $response = $this->get(route('books.show', $this->book));

        $response->assertOk();
        $response->assertViewIs('books.show');
    }

    public function test_書籍詳細に書籍タイトルと著者が表示される(): void
    {
        $book = Book::factory()->create([
            'user_id' => $this->user->id,
            'title' => '表示テスト書籍',
            'author' => '表示テスト著者',
        ]);

        $response = $this->actingAs($this->user)->get(route('books.show', $book));

        $response->assertSee('表示テスト書籍');
        $response->assertSee('表示テスト著者');
    }

    public function test_書籍詳細にジャンルとレビューが表示される(): void
    {
        $genre = Genre::factory()->create(['name' => 'テストジャンル']);
        $this->book->genres()->attach($genre);
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
            'comment' => 'テストコメント',
        ]);

        $response = $this->actingAs($this->user)->get(route('books.show', $this->book));

        $response->assertSee('テストジャンル');
        $response->assertSee('テストコメント');
    }

    public function test_ログイン済みユーザーがお気に入りを追加できる(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('favorites.toggle', $this->book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $other->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_ログイン済みユーザーがお気に入りを解除できる(): void
    {
        $other = User::factory()->create();
        Favorite::create(['user_id' => $other->id, 'book_id' => $this->book->id]);

        $this->actingAs($other)->post(route('favorites.toggle', $this->book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $other->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_ログイン済みユーザーがレビューを投稿できる(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('reviews.store', $this->book), [
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);

        $this->assertDatabaseHas('reviews', [
            'book_id' => $this->book->id,
            'user_id' => $other->id,
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);
    }

    public function test_ログイン済みユーザーがレビューにいいねを追加できる(): void
    {
        $reviewer = User::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $reviewer->id,
        ]);
        $other = User::factory()->create();

        $this->actingAs($other)->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $other->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_ログイン済みユーザーがレビューのいいねを解除できる(): void
    {
        $reviewer = User::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $reviewer->id,
        ]);
        $other = User::factory()->create();
        ReviewLike::create(['user_id' => $other->id, 'review_id' => $review->id]);

        $this->actingAs($other)->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $other->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_本人が自分のレビュー編集画面に遷移できる(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.edit', $review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
    }

    public function test_本人が自分のレビューを削除できる(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_レビュー削除時に関連いいねも削除される(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
        $other = User::factory()->create();
        ReviewLike::create(['user_id' => $other->id, 'review_id' => $review->id]);

        $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
    }

    public function test_本人が自分の書籍の編集画面に遷移できる(): void
    {
        $response = $this->actingAs($this->user)->get(route('books.edit', $this->book));

        $response->assertOk();
        $response->assertViewIs('books.edit');
    }

    public function test_本人が自分の書籍を削除できる(): void
    {
        $this->actingAs($this->user)->delete(route('books.destroy', $this->book));

        $this->assertDatabaseMissing('books', ['id' => $this->book->id]);
    }

    public function test_書籍削除時に関連レビューとお気に入りも削除される(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);
        $other = User::factory()->create();
        ReviewLike::create(['user_id' => $other->id, 'review_id' => $review->id]);
        Favorite::create(['user_id' => $other->id, 'book_id' => $this->book->id]);

        $this->actingAs($this->user)->delete(route('books.destroy', $this->book));

        $this->assertDatabaseMissing('reviews', ['book_id' => $this->book->id]);
        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $this->book->id]);
    }

    // 異常系
    public function test_コメントを空にしてレビューを投稿するとバリデーションエラーになる(): void
    {
        $other = User::factory()->create();

        $response = $this->actingAs($other)->post(route('reviews.store', $this->book), [
            'rating' => 3,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['comment']);
    }

    // 権限系
    public function test_未ログインユーザーがお気に入りボタンを押すとログイン画面にリダイレクトされる(): void
    {
        $response = $this->post(route('favorites.toggle', $this->book));

        $response->assertRedirect(route('login'));
    }

    public function test_未ログインユーザーがレビューを投稿しようとするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->post(route('reviews.store', $this->book), [
            'rating' => 3,
            'comment' => 'コメント',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_未ログインユーザーがいいねボタンを押すとログイン画面にリダイレクトされる(): void
    {
        $review = Review::factory()->create([
            'book_id' => $this->book->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
    }
}
