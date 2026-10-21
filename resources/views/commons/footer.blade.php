<footer class="mt-12 border-t border-base-300 bg-base-200">

    <div class="container mx-auto px-4 py-10">

        <div class="grid grid-cols-1 gap-10 md:grid-cols-2">

            {{-- 店舗情報 --}}
            <section>

                <h2 class="mb-4 text-lg font-bold">
                    店舗情報
                </h2>

                <div class="space-y-2 text-sm leading-relaxed">

                    <p class="font-semibold">
                        {{ config('company.company_name') }}
                    </p>

                    <p>
                        〒{{ config('company.postal_code') }}<br>
                        {{ config('company.address') }}
                    </p>

                    <p>
                        TEL：{{ config('company.phone') }}
                    </p>

                    <p>
                        FAX：{{ config('company.fax') }}
                    </p>

                </div>
                <div>
                    <a href="{{ route('guide.shop') }}"
                        class="link link-hover mt-4 inline-flex items-center gap-2 font-semibold text-primary">
                        店舗案内
                    </a>
                </div>

            </section>

            {{-- ご利用案内 --}}
            <section>

                <h2 class="mb-4 text-lg font-bold">
                    ご利用案内
                </h2>

                <ul class="space-y-3 text-sm">

                    <li>
                        <a href="{{ route('guide.order') }}" class="link link-hover">
                            ご注文方法
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('guide.shipping') }}" class="link link-hover">
                            送料について
                        </a>
                    </li>

                    <li>
                        <a href="#" class="link link-hover">
                            お問い合わせ
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('legal.commerce') }}" class="link link-hover">
                            特定商取引法に基づく表記
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('legal.privacy') }}" class="link link-hover">
                            個人情報保護方針
                        </a>
                    </li>

                </ul>

            </section>

        </div>

        {{-- コピーライト --}}
        <div class="mt-10 border-t border-base-300 pt-5 text-center text-xs text-base-content/60">
            &copy; {{ date('Y') }} 高桑商店 All Rights Reserved.
        </div>

    </div>

</footer>
