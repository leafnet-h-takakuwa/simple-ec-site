<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate(
                [
                    'current_password' => ['required', 'string', 'current_password'],
                    'password' => ['required', 'string', Password::defaults(), 'confirmed'],
                ],
                [
                    'current_password.required' => '現在のパスワードを入力してください。',
                    'current_password.current_password' => '現在のパスワードが正しくありません。',
                    'password.required' => '新しいパスワードを入力してください。',
                    'password.confirmed' => '新しいパスワードと確認用の入力が一致しません。',
                ],
            );
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('success', 'パスワードを変更しました。');

        // 管理者と共通で利用のため、リダイレクト先振り分け
        $this->redirectRoute(Auth::user()->is_admin ? 'admin.dashboard' : 'profile.edit');
    }
}; ?>

<div>
    <form wire:submit="updatePassword" class="space-y-5">
        {{-- 現在のパスワード --}}
        <div>
            <label for="current_password" class="mb-2 block text-sm font-semibold">
                現在のパスワード <span class="text-red-600">*</span>
            </label>
            <input wire:model="current_password" type="password" id="current_password" name="current_password"
                autocomplete="current-password" required class="input w-full">
            @error('current_password')
                <p class="mt-2 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 新しいパスワード --}}
        <div>
            <label for="password" class="mb-2 block text-sm font-semibold">
                新しいパスワード <span class="text-red-600">*</span>
            </label>
            <input wire:model="password" type="password" id="password" name="password" autocomplete="new-password"
                required class="input w-full">
            @error('password')
                <p class="mt-2 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 新しいパスワード（確認） --}}
        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-semibold">
                新しいパスワード（確認） <span class="text-red-600">*</span>
            </label>
            <input wire:model="password_confirmation" type="password" id="password_confirmation"
                name="password_confirmation" autocomplete="new-password" required class="input w-full">
            @error('password_confirmation')
                <p class="mt-2 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 操作ボタン --}}
        <div class="flex justify-end pt-3">
            <button type="submit" class="btn {{ request()->routeIs('admin.*') ? 'btn-success' : 'btn-primary' }}"
                wire:loading.attr="disabled" wire:target="updatePassword">
                パスワードを変更
            </button>
        </div>
    </form>
</div>
