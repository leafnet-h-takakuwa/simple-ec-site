<!DOCTYPE html>
<html lang="ja" data-theme="light" class="scheme-light">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @hasSection('title')
            @yield('title') | {{ config('app.name') }} 管理画面
        @else
            {{ config('app.name') }} 管理画面
        @endif
    </title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body data-theme="light" class="min-h-screen bg-white text-slate-800">

    {{-- ヘッダー --}}
    <header class="sticky top-0 z-50 border-b border-green-200 bg-green-50">
        <div class="flex min-h-20 items-center justify-between gap-4 px-5 lg:px-12">

            {{-- サイト名 --}}
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="truncate text-xl font-bold lg:text-3xl">
                        髙桑商店
                    </span>
                    <span class="shrink-0 text-sm font-bold text-green-800">
                        管理画面
                    </span>
                </a>
            </div>

            {{-- PC用ヘッダーメニュー --}}
            <div class="hidden items-center gap-6 lg:flex">
                <a href="{{ route('home') }}" class="flex items-center gap-2 hover:text-green-700 text-sm">
                    <x-icons.store class="size-5 shrink-0" />
                    ショップを見る
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-sm text-error hover:bg-error/10">
                        <x-icons.log-in class="size-5 shrink-0" />
                        ログアウト
                    </button>
                </form>
            </div>

            {{-- スマホ用ハンバーガーメニュー --}}
            <div class="flex-none lg:hidden">
                <details class="dropdown dropdown-end">
                    <summary class="btn btn-ghost btn-square text-green-800" aria-label="管理メニューを開く">
                        <x-icons.menu class="size-6" />
                    </summary>

                    <ul
                        class="menu dropdown-content z-50 mt-2 w-64 rounded-box border border-green-200 bg-white p-2 text-slate-800 shadow-lg">

                        <li class="menu-title">
                            <span>管理メニュー</span>
                        </li>

                        {{-- ダッシュボード --}}
                        <li>
                            <a href="{{ route('admin.dashboard') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.dashboard'),
                            ])>
                                <x-icons.layout-dashboard class="size-5 shrink-0" />
                                ダッシュボード
                            </a>
                        </li>

                        {{-- 注文管理 --}}
                        <li>
                            <a href="{{ route('admin.orders.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.orders.*'),
                            ])>
                                <x-icons.receipt class="size-5 shrink-0" />
                                注文管理
                            </a>
                        </li>

                        {{-- 商品管理 --}}
                        <li>
                            <a href="{{ route('admin.products.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.products.*'),
                            ])>
                                <x-icons.package class="size-5 shrink-0" />
                                商品管理
                            </a>
                        </li>

                        {{-- FAX注文書管理 --}}
                        <li>
                            <a href="{{ route('admin.fax-order-form.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.fax-order-form.*'),
                            ])>
                                <x-icons.fax class="size-5 shrink-0" />
                                FAX注文書管理
                            </a>
                        </li>

                        {{-- お知らせ管理 --}}
                        <li>
                            <a href="{{ route('admin.notices.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.notices.*'),
                            ])>
                                <x-icons.notice class="size-5 shrink-0" />
                                お知らせ管理
                            </a>
                        </li>

                        {{-- 会員管理 --}}
                        <li>
                            <a href="{{ route('admin.users.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.users.*'),
                            ])>
                                <x-icons.users class="size-5" />
                                会員管理
                            </a>
                        </li>

                        {{-- ショップを見る --}}
                        <li>
                            <a href="{{ route('home') }}" class="gap-3">
                                <x-icons.store class="size-5 shrink-0" />
                                ショップを見る
                            </a>
                        </li>

                        <li>
                            <div class="divider my-1"></div>
                        </li>

                        {{-- ログアウト --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-0">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-error hover:bg-error/10">
                                    <x-icons.log-in class="size-5 shrink-0" />
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    </ul>
                </details>
            </div>
        </div>
    </header>

    <div class="lg:flex">

        {{-- PC用サイドバー --}}
        <aside class="hidden min-h-[calc(100vh-5rem)] w-72 shrink-0 border-r border-green-200 bg-green-50 p-8 lg:block">

            <h2 class="mb-6 text-xl font-bold">
                管理メニュー
            </h2>

            <nav class="flex flex-col gap-2">

                {{-- ダッシュボード --}}
                <a href="{{ route('admin.dashboard') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.dashboard'),
                ])>
                    <x-icons.layout-dashboard class="size-5 shrink-0" />
                    ダッシュボード
                </a>

                {{-- 注文管理 --}}
                <a href="{{ route('admin.orders.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.orders.*'),
                ])>
                    <x-icons.receipt class="size-5 shrink-0" />
                    注文管理
                </a>

                {{-- 商品管理 --}}
                <a href="{{ route('admin.products.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.products.*'),
                ])>
                    <x-icons.package class="size-5 shrink-0" />
                    商品管理
                </a>

                {{-- FAX注文書管理 --}}
                <a href="{{ route('admin.fax-order-form.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.fax-order-form.*'),
                ])>
                    <x-icons.fax class="size-5 shrink-0" />
                    FAX注文書管理
                </a>

                {{-- お知らせ管理 --}}
                <a href="{{ route('admin.notices.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.notices.*'),
                ])>
                    <x-icons.notice class="size-5 shrink-0" />
                    お知らせ管理
                </a>

                {{-- 会員管理 --}}
                <a href="{{ route('admin.users.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.users.*'),
                ])>
                    <x-icons.users class="size-5" />
                    会員管理
                </a>

            </nav>
        </aside>

        {{-- メインコンテンツ --}}
        <main class="min-w-0 flex-1 px-4 py-8 lg:px-12 lg:py-10">
            {{-- 成功・エラーメッセージ --}}
            @include('commons.result_messages')

            @yield('content')
        </main>

    </div>

</body>

</html>
