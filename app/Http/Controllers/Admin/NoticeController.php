<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::latest('published_at')
            ->paginate(10);

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
            'published_at' => ['required', 'date'],
        ]);

        Notice::create($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'お知らせを登録しました。');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
            'published_at' => ['required', 'date'],
        ]);

        $notice->update($validated);

        return redirect()->route('admin.notices.index')
            ->with('success', 'お知らせを更新しました。');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')
            ->with('success', 'お知らせを削除しました。');
    }
}
