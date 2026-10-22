<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderMailService
{
    public function sendReceived(Order $order): void
    {
        $order->loadMissing([
            'orderItems',
            'taxSummaries',
            'orderShippings',
        ]);

        // お客様宛て
        if (filled($order->orderer_email)) {
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
