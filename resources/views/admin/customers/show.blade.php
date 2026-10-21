@extends('layouts.admin')

@section('title', '顧客詳細')

@section('content')
    {{-- タイトル --}}
    <div class="mb-8">
        <a href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center gap-2 text-sm text-green-800 hover:underline">
            <x-arrow direction="left" /> 顧客台帳へ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">顧客詳細</h1>
        <p class="mt-2 text-sm text-slate-500">顧客ID：{{ $customer->id }}</p>
    </div>

    {{-- 登録情報 --}}
    <section class="mb-8 rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-6 text-lg font-bold">
            登録情報
        </h2>

        <dl class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- 氏名 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    氏名
                </dt>
                <dd class="break-words">
                    {{ $customer->name }}
                </dd>
            </div>

            {{-- 利用区分 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    利用区分
                </dt>
                <dd class="flex flex-wrap gap-2">
                    @if ($customer->is_orderer)
                        <span class="badge badge-success badge-outline badge-sm">
                            注文者
                        </span>
                    @endif

                    @if ($customer->is_recipient)
                        <span class="badge badge-success badge-outline badge-sm">
                            配送先
                        </span>
                    @endif
                </dd>
            </div>

            {{-- 郵便番号 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    郵便番号
                </dt>
                <dd>
                    {{ $customer->postal_code }}
                </dd>
            </div>

            {{-- 電話番号 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    電話番号
                </dt>
                <dd>
                    {{ $customer->phone }}
                </dd>
            </div>

            {{-- 住所 --}}
            <div class="lg:col-span-2">
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    住所
                </dt>
                <dd class="whitespace-pre-wrap break-words">{{ $customer->address }}</dd>
            </div>

            {{-- メールアドレス --}}
            <div class="lg:col-span-2">
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    メールアドレス
                </dt>
                <dd class="break-all">
                    {{ $customer->email ?: '未登録' }}
                </dd>
            </div>

            {{-- メモ --}}
            <div class="lg:col-span-2">
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    メモ
                </dt>
                <dd class="whitespace-pre-wrap break-words">{{ $customer->memo ?: 'なし' }}</dd>
            </div>

            {{-- 登録日 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    登録日
                </dt>
                <dd>
                    {{ $customer->created_at->format('Y/m/d H:i') }}
                </dd>
            </div>

            {{-- 最終更新日 --}}
            <div>
                <dt class="mb-2 text-sm font-semibold text-slate-500">
                    最終更新日
                </dt>
                <dd>
                    {{ $customer->updated_at->format('Y/m/d H:i') }}
                </dd>
            </div>
        </dl>
    </section>

    {{-- 操作 --}}
    <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">
            一覧へ戻る
        </a>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-success">
            顧客情報を編集
        </a>
    </div>

@endsection
