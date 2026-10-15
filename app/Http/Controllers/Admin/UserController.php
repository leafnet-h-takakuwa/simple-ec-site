<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('id')->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        $orders = $user->orders()
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.users.show', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}
