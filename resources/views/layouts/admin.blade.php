<!DOCTYPE html>
<html lang="ja" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理画面') | 髙桑商店</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white text-slate-800">

    {{-- ヘッダー --}}
    <header class="border-b border-green-200 bg-green-50">
        <div class="flex min-h-20 items-center justify-between gap-4 px-5 lg:px-12">
            <div class="flex items-center gap-4">
                <span class="text-xl font-bold lg:text-3xl">髙桑商店</span>
                <span class="text-sm font-bold text-green-800">管理画面</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="hidden hover:text-green-700 lg:block">
                    ショップを見る
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm hover:text-green-700">
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="lg:flex">

        {{-- 管理メニュー --}}
        <nav
            class="border-b border-green-200 bg-green-50 p-4 lg:min-h-[calc(100vh-5rem)] lg:w-72 lg:shrink-0 lg:border-r lg:border-b-0 lg:p-8">

            <h2 class="mb-5 hidden text-xl font-bold lg:block">
                管理メニュー
            </h2>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-1 lg:gap-5">
                <a href="{{ route('admin.dashboard') }}" @class([
                    'hover:underline',
                    'font-semibold text-green-800' => request()->routeIs('admin.dashboard'),
                ])>
                    ダッシュボード
                </a>

                <span class="text-slate-500">
                    商品管理
                </span>

                <a href="{{ route('admin.orders.index') }}" @class([
                    'hover:underline',
                    'font-semibold text-green-800' => request()->routeIs('admin.orders.*'),
                ])>
                    注文管理
                </a>

                <a href="{{ url('/') }}" class="hover:underline lg:hidden">
                    ショップを見る
                </a>

            </div>
        </nav>

        {{-- メインコンテンツ --}}
        <main class="min-w-0 flex-1 px-4 py-8 lg:px-12 lg:py-10">
            {{-- 成功・エラーメッセージ --}}
            @include('commons.result_messages')

            @yield('content')
        </main>

    </div>

</body>

</html>
