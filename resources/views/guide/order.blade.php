@extends('layouts.app')

@section('title', 'ご注文方法')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-8">
            {{-- パンくずリスト --}}
            <nav class="mb-5 text-sm text-base-content/60">
                <a href="{{ route('home') }}" class="link link-hover">TOP</a>
                <span class="mx-2">＞</span>
                <span aria-current="page" class="text-base-content">
                    ご注文方法
                </span>
            </nav>

            {{-- ページタイトル --}}
            <h1 class="text-2xl font-bold md:text-3xl">
                ご注文方法
            </h1>
            <p class="mt-3 leading-relaxed text-base-content/70">
                高桑商店では、インターネットまたはFAXからご注文いただけます。<br class="hidden sm:block">
                インターネットからのご注文は、会員登録をしなくてもご利用いただけます。
            </p>
        </div>

        {{-- インターネットでのご注文 --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                インターネットでのご注文
            </h2>

            <div class="space-y-4">
                @foreach ([
            [
                'title' => '商品を選ぶ',
                'description' => "商品一覧・商品詳細ページで、商品の内容・内容量・価格をご確認ください。\nご希望の数量を指定して、カートに商品を追加します。",
            ],
            [
                'title' => 'カートを確認する',
                'description' => "カートに入れた商品名・価格・数量・商品代金の合計をご確認ください。\n数量の変更や商品の削除もできます。ほかの商品を追加する場合は、お買い物を続けてください。",
            ],
            [
                'title' => '注文情報を入力する',
                'description' => "お名前・ご住所・電話番号・メールアドレスなどをご入力ください。\n注文者と異なる住所へのお届けも可能です。包装やお届け日時などのご希望がある場合は、備考欄にご記入ください。",
            ],
            [
                'title' => '注文内容を確認する',
                'description' => "ご注文商品・数量・商品代金・お届け先などをご確認ください。\n送料（代金引換の場合は代引手数料）は別途加算され、お支払い総額は注文受付後に確定します。\nこれらの内容をご確認・ご了承のうえ、ご注文を確定してください。",
            ],
            [
                'title' => 'ご注文完了',
                'description' => "注文完了画面が表示されると、ご注文の受付は完了です。\nご入力いただいたメールアドレス宛に注文受付メールをお送りします。その後、送料・手数料を含むお支払い総額をメールでご案内します。",
            ],
        ] as $step)
                    <div class="flex gap-4 rounded-xl border border-base-300 bg-base-100 p-5 sm:gap-6 sm:p-6">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-lg font-bold text-primary-content">
                            {{ $loop->iteration }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="mb-2 text-lg font-bold">
                                {{ $step['title'] }}
                            </h3>
                            <p class="whitespace-pre-line leading-relaxed text-base-content/75">{{ $step['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    商品一覧を見る
                    <x-arrow direction="right" />
                </a>
            </div>
        </section>

        {{-- お支払いについて --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                お支払いについて
            </h2>

            <p class="mb-5 leading-relaxed">
                お支払い方法は、お届け先によって異なります。
            </p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-base-300 bg-base-100 p-5">
                    <h3 class="mb-3 text-lg font-bold">
                        ご注文者ご本人へのお届け
                    </h3>
                    <span class="badge badge-outline border-base-content/40 mb-3">代金引換</span>
                    <p class="leading-relaxed text-base-content/75">
                        商品のお届け時に、配達員へ代金をお支払いください。<br>
                        商品代金のほかに送料・代引手数料がかかります。
                    </p>
                </div>

                <div class="rounded-xl border border-base-300 bg-base-100 p-5">
                    <h3 class="mb-3 text-lg font-bold">
                        ご注文者と異なるお届け先
                    </h3>
                    <span class="badge badge-outline border-base-content/40 mb-3">銀行振込</span>
                    <p class="leading-relaxed text-base-content/75">
                        贈答用など、別のお届け先をご指定の場合は銀行振込（前払い）となります。<br>
                        送料を含むお支払い総額と振込先は、ご注文受付後にメールでご案内します。<br>
                        ご入金の確認後、商品を発送します。
                    </p>
                </div>
            </div>
        </section>

        {{-- 送料・手数料 --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                送料・手数料について
            </h2>

            <div class="rounded-xl bg-base-200 p-5 sm:p-6">
                <p class="leading-relaxed">
                    ご注文時に表示される商品代金には、送料は含まれておりません。<br>
                    代金引換の場合は、別途代引手数料もかかります。
                </p>
                <p class="mt-3 leading-relaxed">
                    送料は商品の梱包サイズ・重量・配送方法・お届け先などによって異なるため、ご注文受付後に確定します。<br>
                    当店から送料・手数料を含むお支払い総額をメールでご案内します。
                </p>

                <p class="mt-3 leading-relaxed">
                    ご案内した内容についてご不明な点や、キャンセルのご希望がある場合は、速やかに当店までご連絡ください。
                </p>

                <p class="mt-3 leading-relaxed">
                    送料の目安については、以下のページをご確認ください。
                </p>

                <a href="{{ route('guide.shipping') }}"
                    class="link link-hover mt-4 inline-flex items-center gap-2 font-semibold text-primary">
                    送料について
                    <x-arrow direction="right" />
                </a>
            </div>
        </section>

        {{-- FAXでのご注文 --}}
        <section class="mb-12">
            <h2 class="mb-6 border-b border-base-300 pb-3 text-xl font-bold md:text-2xl">
                FAXでのご注文
            </h2>

            <p class="mb-5 leading-relaxed">
                インターネットからのご注文が難しい場合は、FAXでもご注文いただけます。<br>
                下記の注文書をダウンロードして印刷し、必要事項をご記入のうえ、当店までお送りください。
            </p>

            <div class="rounded-xl border border-base-300 bg-base-100 p-5 sm:p-6">
                <p class="mb-4 font-semibold">
                    FAX：{{ config('company.fax') }}
                </p>

                <a href="{{ route('fax-order-form.pdf', ['ts' => now()->timestamp]) }}" target="_blank" rel="noopener"
                    class="btn btn-outline btn-neutral">
                    <x-icons.fax />
                    FAX注文書（PDF）
                </a>
            </div>
        </section>

        {{-- お問い合わせ案内 --}}
        <div class="rounded-xl border border-base-300 p-5 sm:p-6">
            <h2 class="mb-3 text-lg font-bold">
                ご注文についてのお問い合わせ
            </h2>
            <p class="leading-relaxed text-base-content/75">
                ご注文方法についてご不明な点がございましたら、お気軽にお問い合わせください。
            </p>
            <p class="mt-3 font-semibold">
                TEL：{{ config('company.phone') }}
            </p>
        </div>
    </div>

@endsection
