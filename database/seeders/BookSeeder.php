<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '猫の目を通して人間社会の滑稽さを描いた夏目漱石の代表作。ユーモアと風刺に満ちた長編小説。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'description' => '人間関係と影響力の原則を説いた不朽の名著。世界中で読み継がれるビジネス・自己啓発の古典。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'description' => '読みやすく保守しやすいコードを書くための実践的なテクニックを豊富な例とともに解説した技術書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'description' => '個人と職業の両面における成功の原則を7つの習慣として体系的にまとめた自己啓発の名著。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'description' => '江戸っ子気質の若い教師が四国の中学校で奮闘する姿を描いた痛快小説。正義感と人情が光る作品。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'description' => 'ホモ・サピエンスの誕生から現代までの歴史を、認知・農業・科学革命という観点から大胆に描いた歴史書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'description' => 'クリーンなコードを書くための哲学と実践を解説した技術書。ソフトウェア職人気質を追求する開発者必読の一冊。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'description' => 'アドラー心理学を哲人と青年の対話形式でわかりやすく解説。他者の期待に縛られない生き方を説く自己啓発書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'description' => '芸人を目指す若者たちの青春と葛藤を繊細な文体で描いた芥川賞受賞作。笑いと哀愁が交差する純文学。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'description' => 'データに基づいて世界の真実を読み解く方法を説いたベストセラー。思い込みを排し事実に基づいて考える力を養う。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'description' => 'コンテナという箱が世界の貿易と経済をどのように変えたかを丁寧に描いたノンフィクション歴史書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $data) {
            $genreNames = $data['genres'];
            unset($data['genres']);

            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                array_merge($data, ['user_id' => $users->random()->id])
            );

            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
