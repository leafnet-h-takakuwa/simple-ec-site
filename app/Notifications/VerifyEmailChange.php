<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailChange extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $verificationUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【髙桑商店】メールアドレス変更の確認')
            ->text('emails.auth.email-change', [
                'url' => $this->verificationUrl,
            ]);
    }
}
