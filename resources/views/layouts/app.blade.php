<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>Microposts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-base-100">

    {{-- ナビゲーションバー --}}
    @include('commons.navbar')

    {{-- メインレイアウト --}}
    <div class="container mx-auto flex-1 px-4 py-6">

        <div class="flex gap-8">

            {{-- サイドメニュー（PCのみ表示） --}}
            <aside class="hidden lg:block w-64 shrink-0">

                @include('commons.sidebar')

            </aside>

            {{-- メインコンテンツ --}}
            <main class="min-w-0 flex-1">

                {{-- 成功・エラーメッセージ --}}
                @include('commons.result_messages')

                @yield('content')
            </main>
        </div>
    </div>

    {{-- フッター --}}
    @include('commons.footer')

</body>

</html>
