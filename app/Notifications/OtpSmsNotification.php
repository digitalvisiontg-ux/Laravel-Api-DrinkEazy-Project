<?php

namespace App\Notifications;

use App\Providers\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class OtpSmsNotification extends Notification
{
    use Queueable;

    protected string $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['sms'];
    }

    // Cette méthode sera appelée pour l’envoi SMS
    public function toSms($notifiable)
    {
        $service = new TwilioService();
        $service->sendSms($notifiable->phone, "Votre code OTP DrinkEazy est : {$this->otp}");
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
