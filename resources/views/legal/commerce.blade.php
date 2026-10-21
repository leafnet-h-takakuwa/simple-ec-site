@extends('layouts.app')

@section('title', '特定商取引法に基づく表記')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        <div class="mb-8">
            {{-- パンくずリスト --}}
            <nav class="mb-5 text-sm text-base-content/60">
                <a href="{{ route('home') }}" class="link link-hover">TOP</a>
                <span class="mx-2">＞</span>
                <span aria-current="page" class="text-base-content">
                    特定商取引法に基づく表記
                </span>
            </nav>

            {{-- ページタイトル --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold md:text-3xl">
                    特定商取引法に基づく表記
                </h1>
                <p class="mt-3 text-sm leading-relaxed text-base-content/70">
                    当店の通信販売に関するご案内です。
                </p>
            </div>
        </div>

        {{-- 特定商取引法に基づく表記 --}}
        <div class="overflow-hidden rounded-xl border border-base-300 bg-base-100">
            <dl class="divide-y divide-base-200">

                {{-- 販売業者 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">販売業者</dt>
                    <dd>{{ config('company.company_name') }}</dd>
                </div>

                {{-- 販売責任者 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">販売責任者</dt>
                    <dd>{{ config('company.company_manager') }}</dd>
                </div>

                {{-- 所在地 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">所在地</dt>
                    <dd class="leading-relaxed">
                        〒{{ config('company.postal_code') }}<br>
                        {{ config('company.address_sub') }}（事務所）<br>
                        {{ config('company.address') }}（店舗）<br>
                        （株式会社青森魚菜センター内）
                    </dd>
                </div>

                {{-- 電話番号 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">電話番号</dt>
                    <dd>{{ config('company.phone') }}</dd>
                </div>

                {{-- FAX番号 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">FAX番号</dt>
                    <dd>{{ config('company.fax') }}</dd>
                </div>

                {{-- 販売価格 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">販売価格</dt>
                    <dd>各商品ページに税込価格を表示しています。</dd>
                </div>

                {{-- 商品代金以外の費用 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">商品代金以外の費用</dt>
                    <dd class="space-y-2 leading-relaxed">
                        <p>
                            送料、代引手数料（代金引換の場合）、
                            振込手数料（銀行振込の場合）がかかります。
                        </p>
                        <p>
                            送料・代引手数料はご注文受付後に確認し、
                            お支払い総額とあわせてご案内します。
                        </p>
                        <a href="{{ route('guide.shipping') }}" class="link link-hover inline-block text-sm text-primary">
                            送料・手数料について詳しく見る
                        </a>
                    </dd>
                </div>

                {{-- 販売数量 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">販売数量</dt>
                    <dd>1点からご注文いただけます。</dd>
                </div>

                {{-- お支払い方法 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">お支払い方法</dt>
                    <dd class="space-y-2 leading-relaxed">
                        <p>ご注文者ご本人へのお届け：代金引換</p>
                        <p>ご注文者と異なるお届け先へのお届け：銀行振込（前払い）</p>
                    </dd>
                </div>

                {{-- お支払い時期 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">お支払い時期</dt>
                    <dd class="space-y-2 leading-relaxed">
                        <p>代金引換：商品お受け取り時</p>
                        <p>
                            銀行振込：お支払い総額と振込先をご案内した後、
                            商品発送前にお振り込みいただきます。
                        </p>
                    </dd>
                </div>

                {{-- 商品の引渡時期 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">商品の引渡時期</dt>
                    <dd class="leading-relaxed">
                        在庫のある商品は、原則として即日～3日以内に発送します。
                        銀行振込の場合は、ご入金確認後の発送となります。
                        定休日や商品の在庫状況などにより、
                        発送時期が変わる場合があります。
                    </dd>
                </div>

                {{-- 不良品・誤納品への対応 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">不良品・誤納品への対応</dt>
                    <dd class="leading-relaxed">
                        汚損・破損・不良品・誤納品がございましたら、
                        商品到着後3日以内に当店へご連絡ください。
                        状況を確認のうえ、交換または返金にて対応いたします。
                        返品にかかる送料は当店が負担いたします。
                    </dd>
                </div>

                {{-- お客様都合による返品 --}}
                <div class="grid gap-2 p-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:gap-6">
                    <dt class="font-semibold">お客様都合による返品</dt>
                    <dd class="leading-relaxed">
                        【確認中】生鮮食品のため、お客様都合による返品の取り扱いについて確認中です。
                    </dd>
                </div>

            </dl>
        </div>
    </div>
@endsection
