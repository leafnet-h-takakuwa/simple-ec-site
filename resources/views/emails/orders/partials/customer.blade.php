{{-- 注文者と配送先が異なる場合のみ表示 --}}
@if (
$order->orderer_name !== $order->shipping_name ||
$order->orderer_postal_code !== $order->shipping_postal_code ||
$order->orderer_address !== $order->shipping_address ||
$order->orderer_phone !== $order->shipping_phone)
【お届け先】

■ ご住所
〒{{ $order->shipping_postal_code }}
{{ $order->shipping_address }}

■ お名前
{{ $order->shipping_name }}

■ 電話/FAX番号
{{ $order->shipping_phone }}
@endif
【ご注文者様】

■ ご住所
〒{{ $order->orderer_postal_code }}
{{ $order->orderer_address }}

■ お名前
{{ $order->orderer_name }}

■ 電話/FAX番号
{{ $order->orderer_phone }}

■ お支払い方法
{{ $order->payment_method->label() }}

■ 備考
{{ $order->customer_note }}
