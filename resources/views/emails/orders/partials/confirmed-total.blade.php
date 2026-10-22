--------------------------------
商品合計：{{ number_format($order->total_amount) }} 円
送料：{{ number_format($order->shipping_fee) }} 円
@if ($order->payment_method === \App\Enums\PaymentMethod::CASH_ON_DELIVERY)
代引手数料：{{ number_format($order->cod_fee) }} 円
@endif
内消費税：{{ number_format($order->taxSummaries->sum('tax_amount')) }} 円
--------------------------------
お支払い金額：{{ number_format($order->total_amount + $order->shipping_fee + ($order->cod_fee ?? 0)) }} 円
--------------------------------
@include('emails.orders.partials.tax-summary')
--------------------------------
(※)は軽減税率対象
===========================================
