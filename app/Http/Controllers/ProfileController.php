<?php

namespace App\Http\Controllers;

use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'postal_code' => ['nullable', 'string', 'max:' . InputLimits::POSTAL_CODE],
            'address' => ['nullable', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'phone' => ['nullable', 'string', 'max:' . InputLimits::PHONE],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('success', '会員情報を更新しました。');
    }

    /**
     * 退会確認画面
     */
    public function withdraw(Request $request)
    {
        // 管理者の誤操作を防止
        abort_if($request->user()->is_admin, 403);

        return view('profile.withdraw');
    }

    /**
     * 退会処理
     */
    public function destroy(Request $request)
    {
        abort_if($request->user()->is_admin, 403);

        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
        ], [
            'password.required' => '現在のパスワードを入力してください。',
            'password.current_password' => '現在のパスワードが正しくありません。',
        ]);

        // 会員を論理削除（注文記録は残す）
        $request->user()->delete();

        // ログアウトしてセッションを破棄
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', '退会手続きが完了しました。');
    }
}
