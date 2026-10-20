<?php

namespace App\Http\Controllers;

use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::where('is_published', true)
            ->whereDate('published_at', '<=', today())
            ->latest('published_at')
            ->paginate(10);

        return view('notices.index', compact('notices'));
    }

    public function show(Notice $notice)
    {
        abort_unless(
            $notice->is_published &&
                $notice->published_at &&
                $notice->published_at->lte(today()),
            404
        );

        return view('notices.show', compact('notice'));
    }
}
