
@extends('emails.layouts.shop')

@section('content')
{{ $order->orderer_name }} 様

この度はご注文いただきまして誠にありがとうございます。
商品がご用意できましたので、ご注文内容をご確認の上、本メール下部の口座へご入金手続きをお願いいたします。

===========================================
ご注文日時： {{ $order->created_at->format('Y/m/d H:i:s') }}

@include('emails.orders.partials.item-lines')
@include('emails.orders.partials.confirmed-total')

@include('emails.orders.partials.customer')

**************************************
お手数ですが、下記指定の口座まで合計金額のご入金をお願いいたします。
※ お振込み手数料についてはお客様にてご負担お願いいたします
当店の入金確認には土日祝日等により、3～5営業日頂戴することがございます。予めご了承ください。

※ご注意※
必ずご注文者様のお名前でお振込みお願いします。
ご家族のお名前でお振込みの場合確認の遅延、
またご注文者様へのご連絡をさせていただく場合がございます。

[振込口座]
{{ config('company.bank.name') }}({{ config('company.bank.kana') }})
{{ config('company.bank.branch') }}
{{ config('company.bank.account_type') }} {{ config('company.bank.account_number') }}

口座名義：{{ config('company.bank.account_holder') }}

**************************************

ご注文の商品はご入金の確認ができ次第、メールでご連絡の上、
発送させていただきますので、よろしくお願い致します。
@endsection
