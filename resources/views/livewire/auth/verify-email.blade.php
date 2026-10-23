<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth', ['title' => 'メールアドレス確認'])] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <x-auth-header title="メールアドレスの確認" description="ご登録のメールアドレスに確認メールを送信しました。" />

    <div class="space-y-3 text-sm text-gray-600">
        <p>
            メールに記載されたリンクをクリックして、
            メールアドレスの認証を完了してください。
        </p>

        <p>
            メール認証が完了するまでは、注文手続きに進むことができません。
            商品の閲覧やカートへの追加は、認証前でもご利用いただけます。
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="text-center text-sm font-medium text-green-600">
            認証メールを再送信しました。
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            認証メールを再送信
        </flux:button>

        <a href="{{ route('home') }}" class="text-center text-sm text-gray-600 underline hover:text-gray-900">
            ショップへ戻る
        </a>

        <button wire:click="logout" type="button" class="text-sm text-gray-600 underline hover:text-gray-900">
            ログアウト
        </button>
    </div>
</div>
