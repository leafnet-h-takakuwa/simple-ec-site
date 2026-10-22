{{-- 注文明細 --}}
@include('emails.orders.partials.item-lines')
--------------------------------
商品合計：{{ number_format($order->total_amount) }} 円
内消費税：{{ number_format($order->taxSummaries->sum('tax_amount')) }} 円
--------------------------------
※ 送料・代引手数料含まず
(※)は軽減税率対象

@include('emails.orders.partials.tax-summary')
===========================================