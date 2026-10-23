<header class="mb-4">
    <nav class="navbar gap-2 bg-neutral px-4 text-neutral-content">

        {{-- ロゴ --}}
        <div class="min-w-0 flex-1 mt-2">
            <h1>
                <a href="{{ route('home') }}" aria-label="TOP">
                    <x-logo class="w-28 text-white sm:w-40" />
                </a>
            </h1>
        </div>

        {{-- PC用ナビゲーション --}}
        <div class="hidden flex-none md:block">
            <ul class="menu menu-horizontal items-center gap-1 p-0">

                {{-- 管理者ページ --}}
                @if (Auth::check() && Auth::user()->is_admin)
                    <li>
                        <a href="{{ route('admin.dashboard') }}" aria-label="管理画面" @class(['gap-2', 'menu-active' => request()->routeIs('admin.*')])>
                            <x-icons.settings class="size-5 shrink-0" />
                            <span>管理画面</span>
                        </a>
                    </li>
                @endif

                {{-- TOP --}}
                <li>
                    <a href="{{ route('home') }}" aria-label="TOP" @class(['gap-2', 'menu-active' => request()->routeIs('home')])>
                        <x-icons.home class="size-5 shrink-0" />
                        <span>TOP</span>
                    </a>
                </li>

                {{-- 商品一覧 --}}
                <li>
                    <a href="{{ route('products.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('products.*')])>
                        <x-icons.layout-grid class="size-5 shrink-0" />
                        <span>商品一覧</span>
                    </a>
                </li>

                {{-- お知らせ --}}
                <li>
                    <a href="{{ route('notices.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('notices.*')])>
                        <span class="relative inline-flex shrink-0">
                            <x-icons.notice class="size-5" />

                            @if ($newNoticeCount > 0)
                                <span
                                    class="absolute -top-2 -right-2 flex size-4 items-center justify-center rounded-full bg-amber-600 text-[10px] font-bold text-white">
                                    {{ $newNoticeCount > 9 ? '9+' : $newNoticeCount }}
                                </span>
                            @endif
                        </span>

                        <span>お知らせ</span>
                    </a>
                </li>

                {{-- カート --}}
                @if (!(Auth::check() && Auth::user()->is_admin))
                    <li>
                        <a href="{{ route('carts.index') }}" aria-label="カート" @class(['gap-2', 'menu-active' => request()->routeIs('carts.*')])>
                            <x-icons.shopping-cart class="size-5 shrink-0" />
                            <span>カート</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        {{-- スマホ用お知らせ --}}
        <div class="flex-none md:hidden">
            <a href="{{ route('notices.index') }}" class="btn btn-ghost btn-square" aria-label="お知らせ">

                <span class="relative inline-flex">
                    <x-icons.notice class="size-5" />

                    @if ($newNoticeCount > 0)
                        <span
                            class="absolute -top-2 -right-2 flex size-4 items-center justify-center rounded-full bg-amber-600 text-[10px] font-bold text-white">
                            {{ $newNoticeCount > 9 ? '9+' : $newNoticeCount }}
                        </span>
                    @endif
                </span>
            </a>
        </div>

        {{-- スマホ用カート --}}
        @if (!(Auth::check() && Auth::user()->is_admin))
            <div class="flex-none md:hidden">
                <a href="{{ route('carts.index') }}" class="btn btn-ghost btn-square" aria-label="カート">
                    <x-icons.shopping-cart class="size-5 shrink-0" />
                </a>
            </div>
        @endif

        {{-- PC用会員メニュー --}}
        <div class="hidden flex-none md:block">
            <details class="dropdown dropdown-end js-auto-close">
                <summary class="btn btn-ghost btn-sm gap-1 px-2">
                    @auth
                        @if (Auth::user()->is_admin)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-amber-400/40 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-200">
                                <x-icons.shield-check class="h-4 w-4" />
                                管理者モード
                            </span>
                        @else
                            <span class="max-w-40 truncate">
                                {{ Auth::user()->name }} 様
                            </span>
                        @endif
                    @else
                        ログイン／会員登録
                    @endauth

                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </summary>

                <ul
                    class="menu dropdown-content z-50 mt-2 w-52 rounded-box bg-base-100 p-2 text-base-content shadow-lg">
                    @auth
                        @if (!Auth::user()->is_admin)
                            {{-- 注文履歴 --}}
                            <li>
                                <a href="{{ route('orders.index') }}">
                                    <x-icons.receipt class="size-5" />
                                    注文履歴
                                </a>
                            </li>

                            {{-- 会員情報 --}}
                            <li>
                                <a href="{{ route('profile.edit') }}">
                                    <x-icons.user class="size-5" />
                                    会員情報
                                </a>
                            </li>

                            <li>
                                <div class="divider my-1"></div>
                            </li>
                        @endif

                        {{-- ログアウト --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-0">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-error hover:bg-error/10">
                                    <x-icons.log-out class="size-5" />
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    @else
                        {{-- ログイン --}}
                        <li>
                            <a href="{{ route('login') }}">
                                <x-icons.log-in class="size-5 shrink-0" />
                                ログイン
                            </a>
                        </li>

                        {{-- 会員登録 --}}
                        <li>
                            <a href="{{ route('register') }}">
                                <x-icons.user-plus class="size-5" />
                                会員登録
                            </a>
                        </li>
                    @endauth
                </ul>
            </details>
        </div>

        {{-- スマホ用ハンバーガーメニュー --}}
        <div class="flex-none md:hidden">
            <details class="dropdown dropdown-end js-auto-close">
                <summary class="btn btn-ghost btn-square" aria-label="メニューを開く">
                    <x-icons.menu class="size-6" stroke-linejoin="round" />
                </summary>

                <ul
                    class="menu dropdown-content z-50 mt-2 w-60 rounded-box bg-base-100 p-2 text-base-content shadow-lg">

                    @auth
                        <li class="menu-title">
                            @if (Auth::user()->is_admin)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md border border-amber-400/40 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-200">
                                    <x-icons.shield-check class="h-4 w-4" />
                                    管理者モード
                                </span>
                            @else
                                <span class="truncate">{{ Auth::user()->name }} 様</span>
                            @endif
                        </li>
                    @endauth

                    {{-- 管理画面 --}}
                    @if (Auth::check() && Auth::user()->is_admin)
                        <li>
                            <a href="{{ route('admin.dashboard') }}" @class(['gap-2', 'menu-active' => request()->routeIs('admin.*')])>
                                <x-icons.settings class="size-5 shrink-0" />
                                管理画面
                            </a>
                        </li>
                    @endif

                    {{-- TOP --}}
                    <li>
                        <a href="{{ route('home') }}" @class(['gap-2', 'menu-active' => request()->routeIs('home')])>
                            <x-icons.home class="size-5 shrink-0" />
                            TOP
                        </a>
                    </li>

                    {{-- 商品一覧 --}}
                    <li>
                        <a href="{{ route('products.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('products.*')])>
                            <x-icons.layout-grid class="size-5 shrink-0" />
                            商品一覧
                        </a>
                    </li>


                    @auth
                        @if (!Auth::user()->is_admin)
                            {{-- 注文履歴 --}}
                            <li>
                                <a href="{{ route('orders.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('orders.*')])>
                                    <x-icons.receipt class="size-5 shrink-0" />
                                    注文履歴
                                </a>
                            </li>

                            {{-- 会員情報 --}}
                            <li>
                                <a href="{{ route('profile.edit') }}">
                                    <x-icons.user class="size-5" />
                                    会員情報
                                </a>
                            </li>
                        @endif
                    @endauth

                    <li>
                        <div class="divider my-1"></div>
                    </li>

                    @auth
                        {{-- ログアウト --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-0">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-error hover:bg-error/10">
                                    <x-icons.log-out class="size-5 shrink-0" />
                                    ログアウト
                                </button>
                            </form>
                        </li>
                    @else
                        {{-- ログイン --}}
                        <li>
                            <a href="{{ route('login') }}" class="gap-2">
                                <x-icons.log-in class="size-5 shrink-0" />
                                ログイン
                            </a>
                        </li>

                        {{-- 会員登録 --}}
                        <li>
                            <a href="{{ route('register') }}" class="gap-2">
                                <x-icons.user-plus class="size-5 shrink-0" />
                                会員登録
                            </a>
                        </li>
                    @endauth
                </ul>
            </details>
        </div>
    </nav>
</header>
