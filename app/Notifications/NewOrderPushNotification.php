<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderPushNotification extends Notification
{
    public function __construct(
        private Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('新しい注文が入りました')
            ->body(
                '注文番号：#' .
                    sprintf('%08d', $this->order->id) .
                    '　注文内容を確認してください。'
            )
            ->icon('/favicon.svg')
            ->data([
                'url' => route('admin.orders.show', $this->order),
            ]);
    }
}
