# BookShelf 書籍レビューアプリ

## プロジェクトの目的

実務を想定したWebアプリケーション開発と、曖昧な要件から自ら仕様を設計しPMと詳細を詰めるプロセスを経験する

## アプリ概要

書籍レビューアプリケーション「BookShelf」であり、
ユーザーは書籍を登録・閲覧し、レビューの投稿やお気に入り登録ができます。
ジャンルによる分類やレビューへのいいね機能、平均評価に基づくランキング機能も備えています。
外部アプリケーション向けの公開API（JSON）も提供します。

## ER図
![ER図](public/images/erd.png)

## 環境構築手順
1. リポジトリをクローンする
```bash
git clone https://github.com/ta647/BookShelf.git
cd bookshelf-app
```

2. `.env` を用意する
```bash
cp .env.example .env
```
以下の値を編集する
```
DB_HOST=mysql
DB_USERNAME=sail
DB_PASSWORD=password
```

3. 依存パッケージをインストールする
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

4. コンテナを起動する
```bash
./vendor/bin/sail up -d
```

5. アプリケーションキーを生成する
```bash
./vendor/bin/sail artisan key:generate
```

6. マイグレーションと初期データを投入する
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

7. フロントエンドをビルドする
```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

8. ブラウザで http://localhost にアクセスする



## 使用技術

- PHP 8.5 / Laravel 10.x
- MySQL 8.4
- Docker / Laravel Sail
- Laravel Fortify（認証）/ Laravel Sanctum（APIトークン認証）
- Tailwind CSS / Alpine.js / Vite
- Google Books API（ISBN書籍検索）
- PHPUnit（自動テスト）
- Laravel Task Scheduling（日次バッチ処理）

## 作成者
舘岡藍加

## APIエンドポイント一覧
Sanctum / Fortify	
| メソッド | エンドポイント | 内容 | 認証 |
|---|---|---|---|
| GET | `/api/v1/books` | 書籍一覧を取得する | 不要 |
| GET | `/api/v1/books/{book}` | 書籍詳細を取得する | 不要 |
| POST | `/api/v1/books` | 書籍を登録する | 必要（Sanctum） |
| PUT | `/api/v1/books/{book}` | 書籍を更新する（所有者のみ） | 必要（Sanctum） |
| DELETE | `/api/v1/books/{book}` | 書籍を削除する（所有者のみ） | 必要（Sanctum） |

## 開発環境URL
・書籍一覧（トップ）：http://localhost/  
・書籍詳細：http://localhost/books/{book}  
・書籍登録：http://localhost/books/create  
・書籍編集：http://localhost/books/{book}/edit  
・ジャンル一覧：http://localhost/genres  
・ジャンル詳細：http://localhost/genres/{genre}  
・ジャンル登録：http://localhost/genres/create  
・ジャンル編集：http://localhost/genres/{genre}/edit  
・レビュー編集：http://localhost/reviews/{review}/edit  
・お気に入り一覧：http://localhost/favorites  
・ランキング：http://localhost/ranking  
・ログイン：http://localhost/login  
・会員登録：http://localhost/register  
・phpMyAdmin：http://localhost:8080  