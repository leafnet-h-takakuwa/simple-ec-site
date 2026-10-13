<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function create()
    {
        // 管理者は注文不可
        if (Auth::check() && Auth::user()->is_admin === true) {
            abort(403);
        }

        // ログイン中の会員情報（ゲストならnull）
        $user = Auth::user();

        return view('orders.create', [
            'user' => $user,
        ]);
    }
}
