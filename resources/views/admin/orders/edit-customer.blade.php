@extends('layouts.admin')

@section('title', 'お客様情報編集')

@section('content')
    {{-- 注文詳細へ戻る --}}
    <a href="{{ route('admin.orders.show', $order) }}"
        class="mb-6 inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
        <x-arrow direction="left" /> 注文詳細へ戻る
    </a>

    {{-- ページタイトル --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold lg:text-3xl">お客様情報の編集</h1>
        <p class="mt-2 text-sm text-slate-500">
            注文番号 #{{ sprintf('%08d', $order->id) }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.orders.customer.update', $order) }}">
        @csrf
        @method('PUT')

        {{-- 注文者情報・お届け先情報 --}}
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- 注文者情報 --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
                <h2 class="mb-5 text-lg font-bold">注文者情報</h2>

                <div class="space-y-4">
                    <div>
                        <label for="orderer_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_name" name="orderer_name"
                            value="{{ old('orderer_name', $order->orderer_name) }}" maxlength="255" required
                            class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="orderer_postal_code" name="orderer_postal_code"
                            value="{{ old('orderer_postal_code', $order->orderer_postal_code) }}" placeholder="例：030-0000"
                            required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="orderer_address" name="orderer_address" rows="3" required class="textarea w-full">{{ old('orderer_address', $order->orderer_address) }}</textarea>
                    </div>

                    <div>
                        <label for="orderer_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="orderer_phone" name="orderer_phone"
                            value="{{ old('orderer_phone', $order->orderer_phone) }}" required class="input w-full">
                    </div>

                    <div>
                        <label for="orderer_email" class="mb-2 block text-sm font-semibold">
                            メールアドレス（任意）
                        </label>
                        <input type="email" id="orderer_email" name="orderer_email"
                            value="{{ old('orderer_email', $order->orderer_email) }}" class="input w-full">
                    </div>
                </div>
            </section>

            {{-- お届け先情報 --}}
            <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">お届け先情報</h2>

                    <button type="button" id="copy-orderer" class="btn btn-outline btn-neutral btn-sm">
                        注文者情報をコピー
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="shipping_name" class="mb-2 block text-sm font-semibold">
                            氏名 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_name" name="shipping_name"
                            value="{{ old('shipping_name', $order->shipping_name) }}" maxlength="255" required
                            class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_postal_code" class="mb-2 block text-sm font-semibold">
                            郵便番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="shipping_postal_code" name="shipping_postal_code"
                            value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}" placeholder="例：030-0000"
                            required class="input w-full">
                    </div>

                    <div>
                        <label for="shipping_address" class="mb-2 block text-sm font-semibold">
                            住所 <span class="text-red-600">*</span>
                        </label>
                        <textarea id="shipping_address" name="shipping_address" rows="3" required class="textarea w-full">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                    </div>

                    <div>
                        <label for="shipping_phone" class="mb-2 block text-sm font-semibold">
                            電話番号 <span class="text-red-600">*</span>
                        </label>
                        <input type="tel" id="shipping_phone" name="shipping_phone"
                            value="{{ old('shipping_phone', $order->shipping_phone) }}" required class="input w-full">
                    </div>
                </div>
            </section>
        </div>

        {{-- 保存・キャンセル --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-neutral">
                キャンセル
            </a>

            <button type="submit" class="btn btn-success">
                変更を保存
            </button>
        </div>
    </form>

    <script>
        document.getElementById('copy-orderer').addEventListener('click', () => {
            ['name', 'postal_code', 'address', 'phone'].forEach(field => {
                document.getElementById(`shipping_${field}`).value =
                    document.getElementById(`orderer_${field}`).value;
            });
        });
    </script>
@endsection
