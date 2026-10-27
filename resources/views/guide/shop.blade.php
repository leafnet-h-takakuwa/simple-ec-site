@extends('layouts.app')

@section('title', '店舗案内')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        <div class="mb-8">
            {{-- パンくずリスト --}}
            <nav class="mb-5 text-sm text-base-content/60">
                <a href="{{ route('home') }}" class="link link-hover">TOP</a>
                <span class="mx-2">＞</span>
                <span aria-current="page" class="text-base-content">
                    店舗案内
                </span>
            </nav>

            <h1 class="mb-3 text-2xl font-bold lg:text-3xl">
                店舗案内
            </h1>

            <p class="mb-8 text-base-content/70">
                {{ config('company.shop_name') }}の店舗情報・営業時間・アクセスをご案内します。
            </p>
        </div>

        {{-- 店舗情報 --}}
        <section class="mb-10">
            <h2 class="mb-5 text-xl font-bold">店舗情報</h2>

            <div class="overflow-hidden rounded-xl border border-base-300">
                <dl class="divide-y divide-base-300">

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">店舗名</dt>
                        <dd>{{ config('company.shop_name') }}</dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">運営会社</dt>
                        <dd>{{ config('company.company_name') }}</dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">所在地</dt>
                        <dd>
                            〒{{ config('company.postal_code') }}<br>
                            {{ config('company.address') }}<br>
                            （{{ config('company.facility_name_in') }}）
                        </dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">電話番号</dt>
                        <dd>{{ config('company.phone') }}</dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">FAX</dt>
                        <dd>{{ config('company.fax') }}</dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">営業時間</dt>
                        <dd>9:00～17:00</dd>
                    </div>

                    <div class="grid gap-2 p-4 sm:grid-cols-[180px_1fr] sm:p-5">
                        <dt class="font-semibold">定休日</dt>
                        <dd>
                            火曜日（青森魚菜センターの休業日に準じます）
                            <p class="mt-2 text-sm text-base-content/70">
                                祝日・年末年始などは営業日が変更になる場合があります。
                            </p>
                            <a href="https://nokkedon.jp/about/" target="_blank" rel="noopener noreferrer"
                                class="link link-hover mt-2 inline-block text-primary">
                                営業カレンダーを確認する（青森魚菜センター公式サイト）
                            </a>
                        </dd>
                    </div>

                </dl>
            </div>

            <p class="mt-4 text-sm text-base-content/70">
                オンラインでのご注文は24時間受け付けています。<br>
                休業日は商品の発送を行っておりません。
            </p>
        </section>

        {{-- アクセス --}}
        <section>
            <h2 class="mb-5 text-xl font-bold">アクセス</h2>

            <div class="overflow-hidden rounded-xl border border-base-300">
                <iframe title="青森魚菜センター（髙桑商店所在地）"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3019.094673026847!2d140.73335687779263!3d40.82588413063331!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x5f9b9f1ea85cb2dd%3A0xe4bf5340e899e0ae!2z6Z2S5qOu6a2a6I-c44K744Oz44K_44O8!5e0!3m2!1sja!2sjp!4v1749912220609!5m2!1sja!2sjp"
                    class="h-80 w-full border-0 sm:h-[450px]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen>
                </iframe>
            </div>

            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('青森魚菜センター 青森市') }}" target="_blank"
                rel="noopener noreferrer" class="link link-hover mt-3 inline-block text-primary">
                Googleマップで開く
            </a>

            <p class="mt-4 text-sm text-base-content/70">
                JR青森駅から徒歩約5分。
                店舗は青森魚菜センター内にあります。
            </p>

            <a href="https://nokkedon.jp/about/" target="_blank" rel="noopener noreferrer"
                class="link link-hover mt-2 inline-block text-primary">
                青森魚菜センターの店内マップを見る
            </a>
        </section>
    </div>
@endsection
