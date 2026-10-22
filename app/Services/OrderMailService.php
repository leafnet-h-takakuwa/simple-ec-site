<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\View;

class OrderMailService
{
    // ステータス変更時のメール定義
    private const STATUS_MAILS = [
        'bank_transfer' => [
            'received:awaiting_payment' => [
                'type' => 'payment_guide',
                'view' => 'emails.orders.payment-guide',
                'subject' => '[重要]ご入金先のご案内',
            ],
            'preparing:shipped' => [
                'type' => 'shipping_notice',
                'view' => 'emails.orders.shipping-notice',
                'subject' => '商品発送のご案内',
            ],
            'received:cancelled' => [
                'type' => 'order_cancelled',
                'view' => 'emails.orders.cancelled',
                'subject' => 'ご注文キャンセルのお知らせ',
            ],
            'awaiting_payment:cancelled' => [
                'type' => 'order_cancelled',
                'view' => 'emails.orders.cancelled',
                'subject' => 'ご注文キャンセルのお知らせ',
            ],
            'preparing:cancelled' => [
                'type' => 'order_cancelled',
                'view' => 'emails.orders.cancelled',
                'subject' => 'ご注文キャンセルのお知らせ',
            ],
        ],
        'cod' => [
            'received:preparing' => [
                'type' => 'total_confirmed',
                'view' => 'emails.orders.total-confirmed',
                'subject' => 'お支払い金額確定のご案内',
            ],
            'preparing:shipped' => [
                'type' => 'shipping_notice',
                'view' => 'emails.orders.shipping-notice',
                'subject' => '商品発送のご案内',
            ],
            'received:cancelled' => [
                'type' => 'order_cancelled',
                'view' => 'emails.orders.cancelled',
                'subject' => 'ご注文キャンセルのお知らせ',
            ],
            'preparing:cancelled' => [
                'type' => 'order_cancelled',
                'view' => 'emails.orders.cancelled',
                'subject' => 'ご注文キャンセルのお知らせ',
            ],
        ],
    ];

    // メール送信対象の特定
    private function resolveStatusMail(
        Order $order,
        OrderStatus $oldStatus,
        OrderStatus $newStatus
    ): ?array {
        $key = $oldStatus->value . ':' . $newStatus->value;

        $mail = self::STATUS_MAILS[$order->payment_method->value][$key] ?? null;

        if ($mail === null || !View::exists($mail['view'])) {
            return null;
        }

        return $mail;
    }

    // メール送信可能かチェック
    public function canSendForStatus(
        Order $order,
        OrderStatus $newStatus
    ): bool {
        return filled($order->orderer_email)
            && $this->resolveStatusMail(
                $order,
                $order->status,
                $newStatus
            ) !== null;
    }

    // ステータス変更時のメール送信
    public function sendForStatus(
        Order $order,
        OrderStatus $oldStatus,
        OrderStatus $newStatus
    ): void {
        $mail = $this->resolveStatusMail(
            $order,
            $oldStatus,
            $newStatus
        );

        if (blank($order->orderer_email) || $mail === null) {
            throw new \LogicException('このステータス変更ではメールを送信できません。');
        }

        $order->load([
            'orderItems',
            'taxSummaries',
            'orderShippings',
        ]);

        $this->send(
            $order,
            $mail['type'],
            $order->orderer_email,
            '【' . config('company.shop_name') . '】' . $mail['subject'],
            view($mail['view'], compact('order'))->render()
        );
    }

    // 注文受付
    public function sendReceived(Order $order): void
    {
        $order->loadMissing([
            'orderItems',
            'taxSummaries',
            'orderShippings',
        ]);

        // お客様宛て（店頭支払いは送信しない）
        if (
            $order->payment_method !== PaymentMethod::PAY_AT_STORE &&
            filled($order->orderer_email)
        ) {
            $this->send(
                $order,
                'order_received',
                $order->orderer_email,
                '【' . config('company.shop_name') . '】ご注文受付完了',
                view('emails.orders.received', compact('order'))->render()
            );
        }

        // 店舗宛て
        $this->send(
            $order,
            'order_notice',
            config('company.email'),
            '【' . config('company.shop_name') . '】お申込み (' .
                $order->orderer_name . ')',
            view('emails.orders.notice', compact('order'))->render()
        );
    }

    // 送信処理
    private function send(
        Order $order,
        string $type,
        string $to,
        string $subject,
        string $body
    ): void {
        $from = config('mail.from.address');

        $history = $order->mailHistories()->create([
            'type' => $type,
            'status' => 'pending',
            'from_address' => $from,
            'to_address' => $to,
            'subject' => $subject,
            'body' => $body,
        ]);

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            $history->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $history->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('注文メールの送信に失敗しました。', [
                'order_id' => $order->id,
                'type' => $type,
                'exception' => $e,
            ]);
        }
    }
}
