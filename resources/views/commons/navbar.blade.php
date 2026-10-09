<header class="mb-4">
    <nav class="navbar gap-2 bg-neutral px-4 text-neutral-content">

        {{-- ロゴ --}}
        <div class="min-w-0 flex-1">
            <h1>
                <a href="{{ route('home') }}" aria-label="ホーム">
                    <x-logo class="w-28 text-white sm:w-40" />
                </a>
            </h1>
        </div>

        {{-- ナビゲーション --}}
        <div class="flex-none">
            <ul class="menu menu-horizontal items-center gap-1 p-0">

                {{-- ホーム --}}
                <li>
                    <a href="{{ route('home') }}" aria-label="ホーム" @class(['gap-2', 'menu-active' => request()->routeIs('home')])>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z" />
                            <path d="M9 21v-8h6v8" />
                        </svg>
                        <span class="hidden sm:inline">ホーム</span>
                    </a>
                </li>

                {{-- 商品一覧　--}}
                <li>
                    <a href="#" class="gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                        <span class="hidden sm:inline">商品一覧</span>
                    </a>
                </li>

                {{-- カート --}}
                <li>
                    <a href="#" class="gap-2" aria-label="カート">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                        </svg>
                        <span class="hidden sm:inline">カート</span>
                    </a>
                </li>

            </ul>
        </div>

        {{-- 会員メニュー --}}
        <div class="flex-none">
            <details class="dropdown dropdown-end">

                <summary class="btn btn-ghost btn-sm gap-1 px-2">
                    @auth
                        <span class="max-w-24 truncate sm:max-w-40">
                            {{ Auth::user()->name }} 様
                        </span>
                    @else
                        ログイン
                    @endauth

                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </summary>

                <ul
                    class="menu dropdown-content z-50 mt-2 w-52 rounded-box bg-base-100 p-2 text-base-content shadow-lg">

                    @auth
                        {{-- 注文履歴 --}}
                        <li>
                            <a href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
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

                        {{-- ログアウト --}}
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-0">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-error hover:bg-error/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round">
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
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
    </nav>
</header>
