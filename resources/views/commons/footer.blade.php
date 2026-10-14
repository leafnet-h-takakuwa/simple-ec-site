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
                        高桑商店
                    </p>

                    <p>
                        〒000-0000<br>
                        青森県〇〇市〇〇町0-0
                    </p>

                    <p>
                        TEL：000-0000-0000
                    </p>

                    <p>
                        営業時間：9:00〜17:00
                    </p>

                </div>

            </section>

            {{-- ご利用案内 --}}
            <section>

                <h2 class="mb-4 text-lg font-bold">
                    ご利用案内
                </h2>

                <ul class="space-y-3 text-sm">

                    <li>
                        <a href="#" class="link link-hover">
                            ご注文方法
                        </a>
                    </li>

                    <li>
                        <a href="#" class="link link-hover">
                            送料について
                        </a>
                    </li>

                    <li>
                        <a href="#" class="link link-hover">
                            お問い合わせ
                        </a>
                    </li>

                    <li>
                        <a href="#" class="link link-hover">
                            特定商取引法に基づく表記
                        </a>
                    </li>

                    <li>
                        <a href="#" class="link link-hover">
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
