@extends('emails.layouts.shop')

@section('content')
{{ $order->orderer_name }} 様

大変お待たせいたしました。

@if ($order->payment_method === \App\Enums\PaymentMethod::BANK_TRANSFER)
ご入金が確認できましたので、本日ご注文いただいた商品を発送いたしました。
@else
本日、ご注文いただいた商品を発送いたしました。
@endif

ご注文内容と配送情報は以下のとおりです。

===========================================
ご注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.item-lines')
@include('emails.orders.partials.confirmed-total')
@include('emails.orders.partials.customer')

【配送情報】

配送会社：ヤマト運輸
お届け予定日：{{ $order->delivery_date?->format('Y年n月j日') ?? '指定なし' }}
お届け時間帯：{{ $order->delivery_time_slot?->label() ?? '指定なし' }}

@if ($order->orderShippings->count() > 1)
お荷物は {{ $order->orderShippings->count() }} 個口での発送となります。
@endif

@foreach ($order->orderShippings as $shipping)
@if ($order->orderShippings->count() > 1)
[{{ $loop->iteration }}]
@endif
お荷物伝票番号：{{ $shipping->tracking_number }}
追跡URL：https://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010?id={{ $shipping->tracking_number }}

@endforeach
※ 上記URLからお荷物の配送状況をご確認いただけます。

商品到着まで、今しばらくお待ちくださいませ。
またのご利用を心よりお待ちしております。
@endsection