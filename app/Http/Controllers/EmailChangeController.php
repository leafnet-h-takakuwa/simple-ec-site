<?php

namespace App\Http\Controllers;

use App\Notifications\VerifyEmailChange;
use App\Support\InputLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmailChanged;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmailChangeController extends Controller
{
    /**
     * メールアドレス変更申請
     */
    public function update(Request $request)
    {
        $user = $request->user();

        abort_if($user->is_admin, 403);

        $validated = $request->validate([
            'new_email' => [
                'required',
                'email',
                'max:' . InputLimits::SHORT_TEXT,
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => [
                'required',
                'current_password:web',
            ],
        ]);

        $newEmail = $validated['new_email'];

        if (strcasecmp($newEmail, $user->email) === 0) {
            return back()
                ->withErrors(['new_email' => '現在と異なるメールアドレスを入力してください。'])
                ->withInput();
        }

        $token = Str::random(64);

        $user->pending_email = $newEmail;
        $user->pending_email_token = hash('sha256', $token);
        $user->pending_email_requested_at = now();
        $user->save();

        $url = URL::temporarySignedRoute(
            'profile.email.verify',
            now()->addMinutes(60),
            [
                'user' => $user->id,
                'token' => $token,
            ]
        );

        Notification::route('mail', $newEmail)
            ->notify(new VerifyEmailChange($url));

        return redirect()
            ->route('profile.edit')
            ->with('success', '新しいメールアドレスに確認メールを送信しました。');
    }

    /**
     * メールアドレス変更確定
     */
    public function verify(Request $request, int $user, string $token)
    {
        $account = $request->user();

        abort_unless($account && $account->id === $user, 403);
        abort_if($account->is_admin, 403);

        if (
            !$account->pending_email ||
            !$account->pending_email_token ||
            !hash_equals($account->pending_email_token, hash('sha256', $token))
        ) {
            return redirect()->route('profile.edit')
                ->with('error', 'この認証リンクは無効です。');
        }

        if (
            !$account->pending_email_requested_at ||
            $account->pending_email_requested_at->lt(now()->subMinutes(60))
        ) {
            return redirect()->route('profile.edit')
                ->with('error', '認証リンクの有効期限が切れています。');
        }

        if (
            DB::table('users')
            ->where('email', $account->pending_email)
            ->where('id', '!=', $account->id)
            ->exists()
        ) {
            return redirect()->route('profile.edit')
                ->with('error', 'このメールアドレスはすでに使用されています。');
        }

        // 変更前のメールアドレスを退避
        $oldEmail = $account->email;

        $account->email = $account->pending_email;
        $account->email_verified_at = now();
        $account->pending_email = null;
        $account->pending_email_token = null;
        $account->pending_email_requested_at = null;
        $account->save();

        // 変更前のメールアドレスに完了通知
        try {
            Notification::route('mail', $oldEmail)
                ->notify(new EmailChanged());
        } catch (\Throwable $e) {
            // 通知失敗でもメールアドレス変更は取り消さない
            report($e);
        }
        return redirect()->route('home')
            ->with('success', 'メールアドレスの変更が完了しました。');
    }
}
