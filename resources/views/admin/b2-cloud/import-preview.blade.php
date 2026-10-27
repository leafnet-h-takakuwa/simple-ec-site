@extends('layouts.admin')

@section('title', 'CSV取り込み確認')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.b2-cloud.import') }}" class="text-sm text-green-800 hover:underline">
            ← CSV取り込みへ戻る
        </a>

        <h1 class="mt-4 text-2xl font-bold lg:text-3xl">
            CSV取り込み確認
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            B2クラウドの発行結果と登録済みの送り状番号を比較しています。
            この時点ではデータは更新されていません。
        </p>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:p-7">
        <h2 class="mb-5 text-lg font-bold">照合結果</h2>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th>行</th>
                        <th>注文番号</th>
                        <th>個口ID</th>
                        <th>現在の送り状番号</th>
                        <th>CSVの送り状番号</th>
                        <th>判定</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($results as $result)
                        <tr>
                            <td>{{ $result['line'] }}</td>
                            <td>
                                @isset($result['order_id'])
                                    <a href="{{ route('admin.orders.show', $result['order_id']) }}"
                                        class="text-green-700 hover:underline">
                                        #{{ sprintf('%08d', $result['order_id']) }}
                                    </a>
                                @else
                                    -
                                @endisset
                            </td>
                            <td>{{ $result['shipping_id'] ?? '-' }}</td>
                            <td>{{ $result['current_tracking_number'] ?? '未登録' }}</td>
                            <td>{{ $result['tracking_number'] ?? '-' }}</td>
                            <td>
                                @switch($result['status'])
                                    @case('new')
                                        <span class="badge badge-success">新規登録</span>
                                    @break

                                    @case('same')
                                        <span class="badge badge-ghost">変更なし</span>
                                    @break

                                    @case('changed')
                                        <span class="badge badge-warning">差分あり</span>
                                    @break

                                    @default
                                        <span class="badge badge-error">エラー</span>
                                        <p class="mt-1 text-xs text-red-600">
                                            {{ $result['error'] }}
                                        </p>
                                @endswitch

                                @if (!empty($result['warnings']))
                                    <div class="mt-3 space-y-3">
                                        @foreach ($result['warnings'] as $warning)
                                            <div class="rounded-lg border border-amber-300 bg-amber-50 p-3">
                                                <div class="flex items-center gap-2 text-sm font-semibold text-amber-800">
                                                    <x-icons.alert-triangle class="size-4 shrink-0" />
                                                    {{ $warning['label'] }}に差異があります
                                                </div>

                                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-700">
                                                    <span>{{ $warning['current'] ?: '未設定' }}</span>
                                                    <span class="text-slate-400">
                                                        <x-arrow direction="right" />
                                                    </span>
                                                    <span class="font-semibold">{{ $warning['csv'] ?: '未設定' }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    取り込み対象のデータがありません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- 登録ボタン --}}
            <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                <a href="{{ route('admin.b2-cloud.import') }}" class="btn btn-outline btn-neutral">
                    戻る
                </a>

                @if (collect($results)->contains(fn($result) => in_array($result['status'], ['new', 'changed'], true)))
                    <button type="button" class="btn btn-success"
                        onclick="document.getElementById('b2_import_modal').showModal()">
                        送り状番号を登録・更新
                    </button>
                @endif
            </div>
        </section>

        {{-- 送り状番号登録・更新確認モーダル --}}
        <dialog id="b2_import_modal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold" tabindex="-1" autofocus>
                    送り状番号の登録・更新
                </h3>

                <p class="mt-4">
                    CSVの内容を確認し、送り状番号を登録・更新します。
                </p>

                <div class="mt-4 rounded-lg bg-slate-50 p-4">
                    <p class="font-semibold">
                        更新対象は送り状番号のみです。
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        お届け先情報・配送条件・品名・金額などは更新されません。
                    </p>
                </div>

                @if (collect($results)->contains(fn($result) => !empty($result['warnings'])))
                    <p class="mt-4 text-sm text-amber-700">
                        <x-icons.alert-triangle class="mr-1 inline-block size-4 align-text-bottom" />
                        CSVと登録情報に差分がある項目は、自動更新されません。
                        必要に応じて、各注文の詳細画面から個別に修正してください。
                    </p>
                @endif

                <form method="POST" action="{{ route('admin.b2-cloud.import.execute') }}">
                    @csrf

                    <div class="modal-action">
                        <button type="button" class="btn btn-outline btn-neutral"
                            onclick="document.getElementById('b2_import_modal').close()">
                            キャンセル
                        </button>

                        <button type="submit" class="btn btn-success">
                            登録・更新する
                        </button>
                    </div>
                </form>
            </div>

            <form method="dialog" class="modal-backdrop">
                <button>閉じる</button>
            </form>
        </dialog>

    @endsection
