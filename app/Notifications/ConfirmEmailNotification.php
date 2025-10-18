<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmEmailNotification extends Notification
{
    use Queueable;

    protected string $token;

    /**
     * Create a new notification instance.
     */
    protected string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre code OTP DrinkEazy')
            ->line("Voici votre code de vérification : {$this->otp}")
            ->line("Il expire dans 10 minutes.")
            ->line("Si vous n'avez pas demandé ce code, ignorez ce message.");
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
