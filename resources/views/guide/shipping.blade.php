@extends('layouts.app')

@section('title', '送料について')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">

        <div class="mb-8">
            {{-- パンくずリスト --}}
            <nav class="mb-5 text-sm text-base-content/60">
                <a href="{{ route('home') }}" class="link link-hover">TOP</a>
                <span class="mx-2">＞</span>
                <span aria-current="page" class="text-base-content">
                    送料について
                </span>
            </nav>

            {{-- ページタイトル --}}
            <h1 class="text-2xl font-bold md:text-3xl">
                送料について
            </h1>
            <p class="mt-3 leading-relaxed text-base-content/70">
                高桑商店では、ヤマト運輸の宅急便を利用して商品をお届けしています。
            </p>
        </div>

        {{-- 送料のご案内 --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                送料のご案内
            </h2>

            <div class="rounded-xl bg-base-200 p-5 sm:p-6">
                <h3 class="mb-3 text-lg font-bold">
                    送料はご注文受付後に確定します
                </h3>
                <p class="leading-relaxed">
                    送料は、お届け先の地域・荷物のサイズ・重量・配送方法などによって異なります。<br>
                    ご注文受付後、当店で梱包内容を確認し、送料を確定します。
                </p>

                <p class="mt-3 leading-relaxed">
                    ご注文時に表示される商品代金には、送料は含まれておりません。<br>
                    代金引換の場合は、別途代引手数料もかかります。
                </p>

                <p class="mt-3 leading-relaxed">
                    確定した送料・手数料を含むお支払い総額は、メールでご案内します。
                </p>

                <p class="mt-3 leading-relaxed">
                    ご案内したお支払い総額についてご不明な点や、キャンセルのご希望がある場合は、速やかに当店までご連絡ください。
                </p>
            </div>

            <div class="mt-6 rounded-xl border border-base-300 bg-base-100 p-5 sm:p-6">
                <h3 class="mb-3 text-lg font-bold">
                    送料の目安を確認する
                </h3>
                <p class="mb-5 leading-relaxed text-base-content/75">
                    送料の目安は、ヤマト運輸の公式料金表をご参照ください。<br>
                    当店からは青森県より発送いたします。
                </p>

                <a href="{{ config('shipping.yamato_rate_url') }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-outline btn-neutral">
                    ヤマト運輸の送料を確認する
                    <x-arrow direction="right" />
                </a>

                <p class="mt-3 text-xs text-base-content/60">
                    ※ ヤマト運輸の公式サイトが別タブで開きます。<br>
                    実際の送料は当店からのご案内をご確認ください。
                </p>
            </div>
        </section>

        {{-- 梱包・配送について --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                梱包・配送について
            </h2>

            <div class="space-y-4 leading-relaxed">
                <p>
                    商品は、できる限りまとめて梱包し、送料を抑えられるよう努めています。<br>
                    ただし、商品の種類や数量、温度管理の違いによっては、複数のお荷物に分けて発送する場合があります。
                </p>

                <p>
                    生鮮品など温度管理が必要な商品は、クール宅急便でお届けします。<br>
                    その場合は、通常の宅急便運賃に加えてクール便の料金がかかります。
                </p>

                <p>
                    包装方法やお届け日時などにご希望がございましたら、ご注文時の備考欄にご記入ください。<br>
                    可能な範囲で対応いたします。
                </p>
            </div>
        </section>

        {{-- 代引手数料 --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                代引手数料について
            </h2>

            <p class="mb-4 leading-relaxed">
                ご注文者ご本人へのお届けは、代金引換でのお支払いとなります。<br>
                送料とは別に代引手数料がかかります。
            </p>

            <p class="mb-5 leading-relaxed text-base-content/75">
                代引手数料は、商品代金・送料・代引手数料を含むお支払い総額によって異なります。<br>
                詳しくはヤマト運輸の公式案内をご確認ください。
            </p>

            <a href="{{ config('shipping.yamato_cod_fee_url') }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-outline btn-neutral">
                ヤマト運輸の代引手数料を確認する
                <x-arrow direction="right" />
            </a>

            <p class="mt-3 text-xs text-base-content/60">
                ※ ヤマト運輸の公式サイトが別タブで開きます。
            </p>

            <p class="mt-5 leading-relaxed">
                宅急便コレクトでは、1回のお支払い総額は30万円（税込）までとなります。<br>
                この金額には、商品代金・送料・代引手数料が含まれます。
            </p>

            <p class="mt-3 leading-relaxed">
                実際の代引手数料は、ご注文受付後に送料とあわせて確定し、
                お支払い総額をメールでご案内します。
            </p>
        </section>
        {{-- お問い合わせ --}}
        <div class="rounded-xl border border-base-300 p-5 sm:p-6">
            <h2 class="mb-3 text-lg font-bold">
                配送についてのお問い合わせ
            </h2>
            <p class="leading-relaxed text-base-content/75">
                送料や配送方法についてご不明な点がございましたら、お気軽にお問い合わせください。
            </p>
            <p class="mt-3 font-semibold">
                TEL：{{ config('company.phone') }}
            </p>
        </div>
    </div>
@endsection
