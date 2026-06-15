<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();
        $books = Book::all();

        // 山田太郎: 進行中（期日が5日後）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'due_date' => Carbon::today()->addDays(5),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 山田太郎: 進行中（期日が明日 → リマインダー通知対象）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'due_date' => Carbon::today()->addDays(1),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 山田太郎: 完了済み
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'due_date' => Carbon::today()->subDays(10),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => Carbon::today()->subDays(12),
        ]);

        // 山田太郎: 期限切れ（期日が3日前）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[3]->id,
            'due_date' => Carbon::today()->subDays(3),
            'status' => ReadingPlanStatus::Expired,
            'completed_at' => null,
        ]);

        // 山田太郎: 進行中（期日が今日 → バッチ実行で期限切れになる対象）
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[4]->id,
            'due_date' => Carbon::today()->subDays(1),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 鈴木花子: 進行中（認可テスト用：山田太郎はアクセス不可）
        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[5]->id,
            'due_date' => Carbon::today()->addDays(7),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 鈴木花子: 完了済み（認可テスト用）
        ReadingPlan::create([
            'user_id' => $suzuki->id,
            'book_id' => $books[6]->id,
            'due_date' => Carbon::today()->subDays(5),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => Carbon::today()->subDays(6),
        ]);
    }
}
