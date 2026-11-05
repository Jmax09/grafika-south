<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS to the given number.
     *
     * When no gateway is configured (services.sms.enabled = false), the message
     * is written to the log so the OTP flow stays testable without a live
     * provider. Returns true when a delivery attempt was made/logged, false
     * when there is no recipient number.
     */
    public function send(?string $to, string $message): bool
    {
        $to = $to ? trim($to) : null;

        if ($to === null || $to === '') {
            Log::warning('SmsService: no recipient number provided; SMS not sent.');
            return false;
        }

        $enabled  = (bool) config('services.sms.enabled', false);
        $endpoint = config('services.sms.endpoint');
        $apiKey   = config('services.sms.api_key');
        $sender   = config('services.sms.sender_id', 'Grafika');

        if ($enabled && $endpoint) {
            try {
                $response = Http::timeout(15)
                    ->withHeaders(['Authorization' => 'Bearer ' . (string) $apiKey])
                    ->asJson()
                    ->post($endpoint, [
                        'to'      => $to,
                        'message' => $message,
                        'sender'  => $sender,
                    ]);

                Log::info('SmsService: dispatched SMS to ' . $to . ' (HTTP ' . $response->status() . ').');
                return $response->successful();
            } catch (\Throwable $e) {
                Log::error('SmsService: SMS dispatch failed — ' . $e->getMessage());
                return false;
            }
        }

        // Gateway not configured: log the payload (OTP) for development/testing.
        Log::notice("SmsService (gateway disabled): would send SMS to {$to} => {$message}");
        return true;
    }
}
