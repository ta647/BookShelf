<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $reviewData = [
            // 吾輩は猫である (3件)
            ['book' => 1, 'user' => 1, 'rating' => 5, 'comment' => '猫の視点から人間を観察する独自のスタイルが面白く、漱石の文学の深さを感じました。'],
            ['book' => 1, 'user' => 2, 'rating' => 4, 'comment' => 'ユーモアたっぷりで読みやすい。明治時代の雰囲気も伝わってきてとても良かったです。'],
            ['book' => 1, 'user' => 3, 'rating' => 3, 'comment' => '文語体に慣れるまで少し時間がかかりましたが、内容は面白かったです。'],

            // 人を動かす (3件)
            ['book' => 2, 'user' => 2, 'rating' => 5, 'comment' => '人間関係の基本が詰まった一冊。何度読んでも新しい気づきがあります。'],
            ['book' => 2, 'user' => 4, 'rating' => 4, 'comment' => '具体的な事例が多く、すぐに実践できる内容が多かったです。'],
            ['book' => 2, 'user' => 5, 'rating' => 4, 'comment' => 'ビジネスでも日常でも使える知恵が満載です。繰り返し読むべき名著。'],

            // リーダブルコード (3件)
            ['book' => 3, 'user' => 1, 'rating' => 5, 'comment' => 'コードを書く全ての人に読んでほしい。変数名一つの重要性を改めて感じました。'],
            ['book' => 3, 'user' => 3, 'rating' => 5, 'comment' => '具体的なBeforeAfterの例がわかりやすく、すぐに業務に活かせました。'],
            ['book' => 3, 'user' => 5, 'rating' => 4, 'comment' => '読みやすいコードとは何かを体系的に学べる良書。新人エンジニアにもおすすめ。'],

            // 7つの習慣 (3件)
            ['book' => 4, 'user' => 1, 'rating' => 5, 'comment' => '人生の指針となる一冊。定期的に読み返したくなる深い内容です。'],
            ['book' => 4, 'user' => 3, 'rating' => 4, 'comment' => '少し内容が重いですが、読む価値は十分にあります。'],
            ['book' => 4, 'user' => 4, 'rating' => 5, 'comment' => '自己成長に向き合う機会を与えてくれた本。考え方が大きく変わりました。'],

            // 坊っちゃん (2件)
            ['book' => 5, 'user' => 2, 'rating' => 4, 'comment' => '痛快で読んでいて気持ちいい作品。正義感の強い主人公が好きです。'],
            ['book' => 5, 'user' => 5, 'rating' => 3, 'comment' => '夏目漱石作品の中では読みやすい部類。軽快なテンポが楽しい。'],

            // サピエンス全史 (3件)
            ['book' => 6, 'user' => 1, 'rating' => 5, 'comment' => '人類の歴史を壮大なスケールで描いた傑作。世界の見方が変わる一冊です。'],
            ['book' => 6, 'user' => 3, 'rating' => 4, 'comment' => '内容が濃く読み応えがあります。歴史と科学の両方を楽しめました。'],
            ['book' => 6, 'user' => 4, 'rating' => 5, 'comment' => 'ホモ・サピエンスの視点が新鮮。読んだ後に世界の見え方が変わりました。'],

            // Clean Code (2件)
            ['book' => 7, 'user' => 2, 'rating' => 5, 'comment' => 'ソフトウェア開発の哲学を学べる一冊。コードへの向き合い方が変わりました。'],
            ['book' => 7, 'user' => 5, 'rating' => 4, 'comment' => '厳しい基準ですが、それだけ真剣にコードと向き合える内容でした。'],

            // 嫌われる勇気 (3件)
            ['book' => 8, 'user' => 1, 'rating' => 4, 'comment' => 'アドラー心理学をわかりやすく解説。対話形式で読みやすくすんなり頭に入りました。'],
            ['book' => 8, 'user' => 2, 'rating' => 5, 'comment' => '他人の目を気にしすぎていた自分に気づかせてくれた本。考え方が楽になりました。'],
            ['book' => 8, 'user' => 4, 'rating' => 4, 'comment' => '生きることが少し楽になる内容。繰り返し読んで習慣にしたいです。'],

            // 火花 (3件)
            ['book' => 9, 'user' => 3, 'rating' => 4, 'comment' => '芸人という職業の苦悩と情熱がリアルに描かれていて引き込まれました。'],
            ['book' => 9, 'user' => 4, 'rating' => 3, 'comment' => '文体が独特で好みが分かれるかもしれませんが、芸術的な作品だと思います。'],
            ['book' => 9, 'user' => 5, 'rating' => 4, 'comment' => '芥川賞にふさわしい完成度。笑いの中に哀愁があって余韻が残ります。'],

            // FACTFULNESS (3件)
            ['book' => 10, 'user' => 1, 'rating' => 5, 'comment' => 'データで世界を見る視点を教えてくれた本。思い込みがいかに多いか実感しました。'],
            ['book' => 10, 'user' => 2, 'rating' => 5, 'comment' => '世界は思ったより良い方向に進んでいる。希望が持てる内容でした。'],
            ['book' => 10, 'user' => 3, 'rating' => 4, 'comment' => '事実に基づいた思考の大切さを改めて感じた。全ての人に読んでほしい。'],

            // コンテナ物語 (3件)
            ['book' => 11, 'user' => 2, 'rating' => 4, 'comment' => 'コンテナという身近なものがここまで世界を変えたとは知りませんでした。'],
            ['book' => 11, 'user' => 4, 'rating' => 3, 'comment' => '少し専門的な内容もありますが、物流と経済の関係を学べる良書です。'],
            ['book' => 11, 'user' => 5, 'rating' => 4, 'comment' => 'ビジネスの視点でも歴史の視点でも楽しめる一冊。グローバル経済の理解が深まりました。'],
        ];

        foreach ($reviewData as $data) {
            $book = $books[$data['book'] - 1];
            $user = $users[$data['user'] - 1];

            Review::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'],
            ]);
        }
    }
}
