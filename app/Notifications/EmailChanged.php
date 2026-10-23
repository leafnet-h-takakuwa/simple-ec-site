<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChanged extends Notification
{
    use Queueable;

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【髙桑商店】メールアドレス変更完了のお知らせ')
            ->text('emails.auth.email-changed');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
