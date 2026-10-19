<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('id')->paginate(config('shop.pagination.admin_users'));

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        $orders = $user->orders()
            ->orderByDesc('id')
            ->paginate(config('shop.pagination.admin_user_orders'));

        return view('admin.users.show', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }
}
