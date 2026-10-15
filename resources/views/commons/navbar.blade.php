<header class="mb-4">
    <nav class="navbar gap-2 bg-neutral px-4 text-neutral-content">

        {{-- ロゴ --}}
        <div class="min-w-0 flex-1">
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z
                                    M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.86 2.86-.06-.06
                                    A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1.2 1.56V21h-4v-.04
                                    A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06
                                    -2.86-2.86.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.04 13.8H3v-4h.04
                                    A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06
                                    2.86-2.86.06.06A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10.2 3.04V3h4v.04
                                    A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06
                                    2.86 2.86-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.56 1.2H21v4h-.04
                                    A1.7 1.7 0 0 0 19.4 15Z" />
                            </svg>
                            <span>管理画面</span>
                        </a>
                    </li>
                @endif

                {{-- TOP --}}
                <li>
                    <a href="{{ route('home') }}" aria-label="TOP" @class(['gap-2', 'menu-active' => request()->routeIs('home')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z" />
                            <path d="M9 21v-8h6v8" />
                        </svg>
                        <span>TOP</span>
                    </a>
                </li>

                {{-- 商品一覧 --}}
                <li>
                    <a href="{{ route('products.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('products.*')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                        <span>商品一覧</span>
                    </a>
                </li>

                {{-- カート --}}
                @if (!(Auth::check() && Auth::user()->is_admin))
                    <li>
                        <a href="{{ route('carts.index') }}" aria-label="カート" @class(['gap-2', 'menu-active' => request()->routeIs('carts.*')])>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                            </svg>
                            <span>カート</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        {{-- スマホ用カート --}}
        @if (!(Auth::check() && Auth::user()->is_admin))
            <div class="flex-none md:hidden">
                <a href="{{ route('carts.index') }}" class="btn btn-ghost btn-square" aria-label="カート">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                    </svg>
                </a>
            </div>
        @endif

        {{-- PC用会員メニュー --}}
        <div class="hidden flex-none md:block">
            <details class="dropdown dropdown-end">
                <summary class="btn btn-ghost btn-sm gap-1 px-2">
                    @auth
                        @if (Auth::user()->is_admin)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-amber-400/40 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 3 4.5 6v5.25c0 5.25 3.45 8.55 7.5 9.75 4.05-1.2 7.5-4.5 7.5-9.75V6L12 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                                </svg>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 3H5a2 2 0 0 0-2 2v16l4-2 5 2 5-2 4 2V5a2 2 0 0 0-2-2h-3" />
                                        <path d="M8 3h8v4H8z" />
                                        <path d="M8 12h8M8 16h5" />
                                    </svg>
                                    注文履歴
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
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
                    @else
                        {{-- ログイン --}}
                        <li>
                            <a href="{{ route('login') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m10 17 5-5-5-5" />
                                    <path d="M15 12H3" />
                                </svg>
                                ログイン
                            </a>
                        </li>

                        {{-- 会員登録 --}}
                        <li>
                            <a href="{{ route('register') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="9" cy="8" r="4" />
                                    <path d="M2 21v-2a7 7 0 0 1 14 0v2" />
                                    <path d="M19 8v6M16 11h6" />
                                </svg>
                                会員登録
                            </a>
                        </li>
                    @endauth
                </ul>
            </details>
        </div>

        {{-- スマホ用ハンバーガーメニュー --}}
        <div class="flex-none md:hidden">
            <details class="dropdown dropdown-end">
                <summary class="btn btn-ghost btn-square" aria-label="メニューを開く">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </summary>

                <ul
                    class="menu dropdown-content z-50 mt-2 w-60 rounded-box bg-base-100 p-2 text-base-content shadow-lg">

                    @auth
                        <li class="menu-title">
                            @if (Auth::user()->is_admin)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md border border-amber-400/40 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-700 dark:text-amber-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3 4.5 6v5.25c0 5.25 3.45 8.55 7.5 9.75 4.05-1.2 7.5-4.5 7.5-9.75V6L12 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                                    </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z
                                        M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.86 2.86-.06-.06
                                        A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1.2 1.56V21h-4v-.04
                                        A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06
                                        -2.86-2.86.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.04 13.8H3v-4h.04
                                        A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06
                                        2.86-2.86.06.06A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10.2 3.04V3h4v.04
                                        A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06
                                        2.86 2.86-.06.06A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.56 1.2H21v4h-.04
                                        A1.7 1.7 0 0 0 19.4 15Z" />
                                </svg>
                                管理画面
                            </a>
                        </li>
                    @endif

                    {{-- TOP --}}
                    <li>
                        <a href="{{ route('home') }}" @class(['gap-2', 'menu-active' => request()->routeIs('home')])>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z" />
                                <path d="M9 21v-8h6v8" />
                            </svg>
                            TOP
                        </a>
                    </li>

                    {{-- 商品一覧 --}}
                    <li>
                        <a href="{{ route('products.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('products.*')])>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7" rx="1" />
                                <rect x="14" y="3" width="7" height="7" rx="1" />
                                <rect x="3" y="14" width="7" height="7" rx="1" />
                                <rect x="14" y="14" width="7" height="7" rx="1" />
                            </svg>
                            商品一覧
                        </a>
                    </li>

                    {{-- 注文履歴 --}}
                    @auth
                        @if (!Auth::user()->is_admin)
                            <li>
                                <a href="{{ route('orders.index') }}" @class(['gap-2', 'menu-active' => request()->routeIs('orders.*')])>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M8 3H5a2 2 0 0 0-2 2v16l4-2 5 2 5-2 4 2V5a2 2 0 0 0-2-2h-3" />
                                        <path d="M8 3h8v4H8z" />
                                        <path d="M8 12h8M8 16h5" />
                                    </svg>
                                    注文履歴
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
                    @else
                        {{-- ログイン --}}
                        <li>
                            <a href="{{ route('login') }}" class="gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m10 17 5-5-5-5" />
                                    <path d="M15 12H3" />
                                    <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7" />
                                </svg>
                                ログイン
                            </a>
                        </li>

                        {{-- 会員登録 --}}
                        <li>
                            <a href="{{ route('register') }}" class="gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="9" cy="8" r="4" />
                                    <path d="M2 21v-2a7 7 0 0 1 14 0v2" />
                                    <path d="M19 8v6M16 11h6" />
                                </svg>
                                会員登録
                            </a>
                        </li>
                    @endauth
                </ul>
            </details>
        </div>
    </nav>
</header>
