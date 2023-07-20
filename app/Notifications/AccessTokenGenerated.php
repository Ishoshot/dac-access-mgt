<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccessTokenGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $accessToken;

    /**
     * Create a new notification instance.
     */
    public function __construct(object $accessToken)
    {
        $this->accessToken = $accessToken;
    }

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
        return (new MailMessage)->subject('Digital Assistant Chatbot: Access Token Generated')
            ->markdown(
                'mail.accessToken.generated',
                [
                    'accessToken' => $this->accessToken,
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
