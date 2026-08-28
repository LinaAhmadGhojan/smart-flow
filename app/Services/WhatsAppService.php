<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp text message via Ultramsg.
     * Silently no-ops (and logs) if the integration isn't configured/enabled,
     * so appointment creation/update never fails because of it.
     */
    public function send(?string $phone, string $message): bool
    {
        if (!config('whatsapp.enabled')) {
            return false;
        }

        $to = $this->normalizeNumber($phone);
        if (!$to) {
            return false;
        }

        $instanceId = config('whatsapp.ultramsg.instance_id');
        $token = config('whatsapp.ultramsg.token');
        $baseUrl = rtrim(config('whatsapp.ultramsg.base_url'), '/');

        if (!$instanceId || !$token) {
            Log::warning('WhatsApp notification skipped: Ultramsg is not configured.');
            return false;
        }

        try {
            $response = Http::asForm()->post("{$baseUrl}/{$instanceId}/messages/chat", [
                'token' => $token,
                'to' => $to,
                'body' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp notification failed', [
                    'to' => $to,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp notification error: ' . $e->getMessage());
            return false;
        }
    }

    /** Normalize a local (UAE-style) phone number to international digits-only format. */
    private function normalizeNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (!$digits) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '971' . substr($digits, 1);
        } elseif (strlen($digits) <= 10 && !str_starts_with($digits, '971')) {
            $digits = '971' . $digits;
        }

        return $digits;
    }
}
