<?php

namespace App\Providers;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->twilio->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $message,
            ]);
            Log::info("Twilio SMS envoyé à {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur Twilio : " . $e->getMessage());
            return false;
        }
    }
}
