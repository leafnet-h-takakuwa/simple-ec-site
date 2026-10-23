<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MailStatus;
use App\Enums\MailType;
use App\Http\Controllers\Controller;
use App\Models\MailHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MailHistoryController extends Controller
{
    /**
     * メール送信履歴一覧
     */
    public function index(Request $request)
    {
        $conditions = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', Rule::enum(MailType::class)],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(MailStatus::class)],
        ]);

        $mail_histories = MailHistory::query()
            ->when(
                filled($conditions['date_from'] ?? null),
                fn($query) => $query->whereDate('created_at', '>=', $conditions['date_from'])
            )
            ->when(
                filled($conditions['date_to'] ?? null),
                fn($query) => $query->whereDate('created_at', '<=', $conditions['date_to'])
            )
            ->when(
                filled($conditions['type'] ?? null),
                fn($query) => $query->where('type', $conditions['type'])
            )
            ->when(
                filled($conditions['address'] ?? null),
                fn($query) => $query->where('to_address', 'like', '%' . $conditions['address'] . '%')
            )
            ->when(
                filled($conditions['status'] ?? null),
                fn($query) => $query->where('status', $conditions['status'])
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(config('shop.pagination.orders'))
            ->withQueryString();

        return view('admin.mail_histories.index', [
            'mail_histories' => $mail_histories,
            'conditions' => $conditions,
            'mailTypes' => MailType::cases(),
            'mailStatuses' => MailStatus::cases(),
        ]);
    }

    /**
     * メール送信履歴詳細
     */
    public function show(MailHistory $mailHistory)
    {
        $mailHistory->load([
            'related',
            'user',
        ]);

        return view('admin.mail_histories.show', [
            'mail_history' => $mailHistory,
        ]);
    }
}
