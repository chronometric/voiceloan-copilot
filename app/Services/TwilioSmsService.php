<?php

namespace App\Services;

use App\Models\Borrower;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TwilioSmsService
{
    /**
     * @return array{ok: bool, twilio_sid?: string, error?: string, detail?: mixed}
     */
    public function sendToBorrower(Borrower $borrower, string $body, ?string $toOverride = null): array
    {
        $sid = config('twilio.account_sid');
        $token = config('twilio.auth_token');
        $from = config('twilio.from');

        if ($sid === null || $sid === '' || $token === null || $token === '' || $from === null || $from === '') {
            return ['ok' => false, 'error' => 'twilio_not_configured'];
        }

        $rawTo = $toOverride ?? $borrower->phone;
        if ($rawTo === null || trim((string) $rawTo) === '') {
            return ['ok' => false, 'error' => 'missing_phone'];
        }

        $to = $this->normalizeE164((string) $rawTo);
        $footer = trim((string) config('compliance.sms_footer', ''));
        if ($footer !== '') {
            $body = rtrim($body)."\n\n".$footer;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post($url, [
                'From' => $from,
                'To' => $to,
                'Body' => $body,
            ]);

        if (! $response->successful()) {
            AuditLogger::logBorrowerEvent($borrower->id, 'sms_failed', 'sms.outbound', PiiRedactor::redactSmsAuditPayload([
                'to' => $to,
                'http_status' => $response->status(),
                'twilio_body' => Str::limit($response->body(), 500),
            ]));

            return [
                'ok' => false,
                'error' => 'twilio_error',
                'detail' => $response->json(),
            ];
        }

        $json = $response->json();
        $twilioSid = $json['sid'] ?? null;

        AuditLogger::logBorrowerEvent($borrower->id, 'sms_sent', 'sms.outbound', PiiRedactor::redactSmsAuditPayload([
            'to' => $to,
            'twilio_sid' => $twilioSid,
            'body_preview' => Str::limit($body, 160),
        ]));

        return ['ok' => true, 'twilio_sid' => $twilioSid];
    }

    private function normalizeE164(string $phone): string
    {
        $p = preg_replace('/\s+/', '', $phone) ?? '';
        if (str_starts_with($p, '+')) {
            return $p;
        }

        $digits = preg_replace('/\D+/', '', $p) ?? '';
        if (strlen($digits) === 10) {
            return '+1'.$digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+'.$digits;
        }

        return $p;
    }
}
