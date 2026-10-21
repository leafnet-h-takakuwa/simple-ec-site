<aside class="rounded-lg border border-base-300 bg-base-100 p-4">

    {{-- 商品カテゴリ --}}
    <h2 class="mb-4 border-b border-base-300 pb-3 text-lg font-bold">
        商品を探す
    </h2>

    <ul class="menu w-full gap-1 p-0">

        <li>
            <a href="{{ route('products.index') }}"
                class="rounded-lg {{ request()->routeIs('products.index') && request()->query('category') === null ? 'menu-active' : '' }}">
                すべての商品
            </a>
        </li>

        {{-- カテゴリ別リンク --}}
        @foreach (\App\Enums\ProductCategory::cases() as $category)
            <li>
                <a href="{{ route('products.index', ['category' => $category->value]) }}"
                    class="rounded-lg {{ request()->routeIs('products.index') && request()->query('category') === $category->value ? 'menu-active' : '' }}">
                    {{ $category->label() }}
                </a>
            </li>
        @endforeach

    </ul>

    {{-- ご利用案内 --}}
    <div class="mt-6 border-t border-base-300 pt-4">

        <h2 class="mb-3 text-lg font-bold">
            ご利用案内
        </h2>

        <ul class="menu w-full gap-1 p-0">

            <li>
                <a href="{{ route('guide.order') }}" class="rounded-lg">
                    ご注文方法
                </a>
            </li>

            <li>
                <a href="{{ route('guide.shipping') }}" class="rounded-lg">
                    送料について
                </a>
            </li>

        </ul>

    </div>
    {{-- ご利用案内 --}}
    <div class="mt-6 border-t border-base-300 pt-4">
        <ul class="menu w-full gap-1 p-0">
            <li>
                <a href="{{ route('guide.shop') }}" class="rounded-lg">
                    店舗案内
                </a>
            </li>
        </ul>
    </div>

</aside>
