<?php

namespace App\Services;

class PiiRedactor
{
    /**
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>|null
     */
    public static function redactAudit(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $keys = config('pii.audit_redact_keys', []);
        $token = (string) config('pii.mask_token', '[redacted]');
        $out = $data;
        foreach ($keys as $key) {
            if (array_key_exists($key, $out) && $out[$key] !== null) {
                $out[$key] = $token;
            }
        }

        return $out;
    }

    /**
     * Mask E.164 or raw phone for audit / logs (keeps last 2 digits when possible).
     */
    public static function maskPhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) >= 2) {
            return '***'.substr($digits, -2);
        }

        return (string) config('pii.mask_token', '[redacted]');
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public static function maskUrlaSnapshot(string $path, $value)
    {
        $paths = config('pii.urla_snapshot_redact_paths', []);
        if (! in_array($path, $paths, true)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return $value;
        }

        return (string) config('pii.mask_token', '[redacted]');
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public static function maskSsnForVoiceJson($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return (string) config('pii.mask_token', '[redacted]');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function redactSmsAuditPayload(array $payload): array
    {
        if (isset($payload['to'])) {
            $payload['to'] = self::maskPhone((string) $payload['to']);
        }

        return $payload;
    }
}
