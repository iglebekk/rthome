<?php

namespace App\Notifications;

use App\Models\MemberActivation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberActivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MemberActivation $activation,
        public string $plainToken,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url()->temporarySignedRoute(
            'activation.show',
            $this->activation->expires_at,
            ['activation' => $this->activation, 'token' => $this->plainToken],
        );

        return (new MailMessage)
            ->subject(__('auth.activation.email_subject'))
            ->line(__('auth.activation.email_intro'))
            ->action(__('auth.activation.email_action'), $url)
            ->line(__('auth.activation.email_expiry'));
    }
}
