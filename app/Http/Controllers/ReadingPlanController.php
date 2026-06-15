<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧を表示する（状態による絞り込み対応）
     */
    public function index(): View
    {
        $currentStatus = request('status');

        $query = Auth::user()->readingPlans()->with('book');

        if ($currentStatus) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->latest()->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画作成フォームを表示する
     */
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        Auth::user()->readingPlans()->create([
            'book_id'  => $request->book_id,
            'due_date' => $request->target_date,
            'status'   => ReadingPlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    /**
     * 読書計画編集フォームを表示する
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        return view('reading-plans.edit', compact('plan'));
    }

    /**
     * 読書計画の期日を更新する
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update(['due_date' => $request->target_date]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画を読了済みにする
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status'       => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読了しました！');
    }
}
