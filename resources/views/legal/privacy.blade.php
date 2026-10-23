@extends('layouts.app')

@section('title', '個人情報保護方針')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        <div class="mb-8">
            {{-- パンくずリスト --}}
            <nav class="mb-5 text-sm text-base-content/60">
                <a href="{{ route('home') }}" class="link link-hover">TOP</a>
                <span class="mx-2">＞</span>
                <span aria-current="page" class="text-base-content">
                    個人情報保護方針
                </span>
            </nav>

            <h1 class="mb-8 text-2xl font-bold lg:text-3xl">
                個人情報保護方針
            </h1>
        </div>

        <div class="space-y-8 leading-relaxed">

            <p>
                有限会社髙桑商店（以下「当社」といいます。）は、
                お客様の個人情報を適切に取り扱い、保護することを重要な責務と考え、
                以下のとおり個人情報保護方針を定めます。
            </p>

            {{-- 個人情報の取得 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">1. 個人情報の取得</h2>
                <p>
                    当社は、商品のご注文、会員登録、お問い合わせなどの際に、
                    氏名、住所、電話番号、メールアドレスその他必要な個人情報を取得します。
                </p>
            </section>

            {{-- 利用目的 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">2. 個人情報の利用目的</h2>
                <p class="mb-3">
                    当社は、取得した個人情報を以下の目的で利用します。
                </p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>ご注文の受付、確認および管理</li>
                    <li>商品の発送および配送に関する連絡</li>
                    <li>送料・手数料を含むお支払い総額のご案内</li>
                    <li>ご注文内容や発送状況に関するメールの送信</li>
                    <li>会員登録、ログイン認証および注文履歴の管理</li>
                    <li>お問い合わせへの対応</li>
                    <li>返品、交換、返金などの対応</li>
                    <li>その他、上記の目的に付随する業務</li>
                </ul>
            </section>

            {{-- 個人情報の管理 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">3. 個人情報の管理</h2>
                <p>
                    当社は、個人情報の漏えい、紛失、改ざん、不正アクセスなどを防止するため、
                    必要かつ適切な安全管理措置を講じ、個人情報を適切に管理します。
                </p>
            </section>

            {{-- 第三者提供 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">4. 個人情報の第三者提供</h2>
                <p class="mb-3">
                    当社は、次の場合を除き、お客様の個人情報を第三者に提供しません。
                </p>
                <ul class="list-disc space-y-2 pl-6">
                    <li>お客様の同意がある場合</li>
                    <li>法令に基づく場合</li>
                    <li>
                        商品の配送など、利用目的の達成に必要な範囲で
                        業務を委託する場合
                    </li>
                    <li>その他、法令により認められる場合</li>
                </ul>
            </section>

            {{-- 開示・訂正・削除 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">5. 個人情報の開示・訂正・削除等</h2>
                <p>
                    お客様ご本人から、個人情報の開示、訂正、利用停止、削除などの
                    お申し出があった場合には、ご本人であることを確認のうえ、
                    法令に従い適切に対応します。
                </p>
            </section>

            {{-- Cookie --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">6. Cookie（クッキー）の利用</h2>
                <p>
                    当サイトでは、ログイン状態の維持、ショッピングカートの管理、
                    セキュリティの確保など、サイトの機能を提供するために
                    Cookieを使用します。
                </p>
            </section>

            {{-- 法令遵守 --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">7. 法令の遵守と見直し</h2>
                <p>
                    当社は、個人情報の取扱いに関する法令その他の規範を遵守し、
                    本方針の内容を必要に応じて見直し、改善に努めます。
                </p>
            </section>

            {{-- お問い合わせ --}}
            <section>
                <h2 class="mb-3 text-lg font-bold">8. お問い合わせ窓口</h2>
                <p class="mb-4">
                    個人情報の取扱いに関するお問い合わせは、
                    以下の窓口までご連絡ください。
                </p>

                <div class="rounded-xl border border-base-300 p-5">
                    <p class="font-bold">{{ config('company.company_name') }}</p>

                    <p class="mt-2">
                        〒{{ config('company.postal_code') }}<br>
                        {{ config('company.address') }}<br>
                        （{{ config('company.facility_name') }}内）
                    </p>

                    <p class="mt-3">
                        TEL：{{ config('company.phone') }}<br>
                        FAX：{{ config('company.fax') }}<br>
                        <a href="mailto:{{ config('company.email') }}"
                            class="inline-flex items-center gap-2 hover:underline">
                            <x-icons.mail class="size-5" />
                            {{ config('company.email') }}
                        </a>
                    </p>
                </div>
            </section>
        </div>
    </div>
@endsection
