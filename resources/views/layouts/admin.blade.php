<!DOCTYPE html>
<html lang="ja" data-theme="light" class="scheme-light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理画面') | 髙桑商店</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body data-theme="light" class="min-h-screen bg-white text-slate-800">

    {{-- ヘッダー --}}
    <header class="relative z-50 border-b border-green-200 bg-green-50">
        <div class="flex min-h-20 items-center justify-between gap-4 px-5 lg:px-12">

            {{-- サイト名 --}}
            <div class="flex min-w-0 items-center gap-3">
                <span class="truncate text-xl font-bold lg:text-3xl">
                    髙桑商店
                </span>
                <span class="shrink-0 text-sm font-bold text-green-800">
                    管理画面
                </span>
            </div>

            {{-- PC用ヘッダーメニュー --}}
            <div class="hidden items-center gap-6 lg:flex">
                <a href="{{ route('home') }}" class="flex items-center gap-2 hover:text-green-700 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10V21h18V10" />
                        <path d="M2 10l2-7h16l2 7" />
                        <path d="M2 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 2-3" />
                        <path d="M9 21v-7h6v7" />
                    </svg>
                    ショップを見る
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-sm text-error hover:bg-error/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M10 17l5-5-5-5" />
                            <path d="M15 12H3" />
                            <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
                        </svg>
                        ログアウト
                    </button>
                </form>
            </div>

            {{-- スマホ用ハンバーガーメニュー --}}
            <div class="flex-none lg:hidden">
                <details class="dropdown dropdown-end">
                    <summary class="btn btn-ghost btn-square text-green-800" aria-label="管理メニューを開く">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="9" rx="1" />
                                    <rect x="14" y="3" width="7" height="5" rx="1" />
                                    <rect x="14" y="12" width="7" height="9" rx="1" />
                                    <rect x="3" y="16" width="7" height="5" rx="1" />
                                </svg>
                                ダッシュボード
                            </a>
                        </li>

                        {{-- 商品管理 --}}
                        <li>
                            <a href="{{ route('admin.products.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.products.*'),
                            ])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    <path d="m3.3 7 8.7 5 8.7-5M12 22V12" />
                                </svg>
                                商品管理
                            </a>
                        </li>

                        {{-- 注文管理 --}}
                        <li>
                            <a href="{{ route('admin.orders.index') }}" @class([
                                'gap-3',
                                'menu-active' => request()->routeIs('admin.orders.*'),
                            ])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M8 3H5a2 2 0 0 0-2 2v16l4-2 5 2 5-2 4 2V5a2 2 0 0 0-2-2h-3" />
                                    <path d="M8 3h8v4H8z" />
                                    <path d="M8 12h8M8 16h5" />
                                </svg>
                                注文管理
                            </a>
                        </li>

                        {{-- ショップを見る --}}
                        <li>
                            <a href="{{ route('home') }}" class="gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 10V21h18V10" />
                                    <path d="M2 10l2-7h16l2 7" />
                                    <path d="M2 10a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 2-3" />
                                    <path d="M9 21v-7h6v7" />
                                </svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 17l5-5-5-5" />
                                        <path d="M15 12H3" />
                                        <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
                                    </svg>
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
        <aside
            class="hidden min-h-[calc(100vh-5rem)] w-72 shrink-0 border-r border-green-200 bg-green-50 p-8 lg:block">

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
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1" />
                        <rect x="14" y="3" width="7" height="5" rx="1" />
                        <rect x="14" y="12" width="7" height="9" rx="1" />
                        <rect x="3" y="16" width="7" height="5" rx="1" />
                    </svg>
                    ダッシュボード
                </a>

                {{-- 商品管理 --}}
                <a href="{{ route('admin.products.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.products.*'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                        <path d="m3.3 7 8.7 5 8.7-5M12 22V12" />
                    </svg>
                    商品管理
                </a>

                {{-- 注文管理 --}}
                <a href="{{ route('admin.orders.index') }}" @class([
                    'flex items-center gap-3 rounded-lg px-4 py-3 transition-colors hover:bg-green-100',
                    'bg-green-100 font-semibold text-green-800' => request()->routeIs(
                        'admin.orders.*'),
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v16l4-2 5 2 5-2 4 2V5a2 2 0 0 0-2-2h-3" />
                        <path d="M8 3h8v4H8z" />
                        <path d="M8 12h8M8 16h5" />
                    </svg>
                    注文管理
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
