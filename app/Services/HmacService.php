<?php

namespace App\Services;

class HmacService
{
    public static function generate(array $data, ?string $secret = null): string
    {
        $secret = $secret ?? config('app.hmac_secret');
        ksort($data);
        $payload = implode('|', $data);
        return hash_hmac('sha256', $payload, $secret);
    }

    public static function verify(array $data, string $signature, ?string $secret = null): bool
    {
        return hash_equals(self::generate($data, $secret), $signature);
    }

    public static function sign(array $data, string $secret): string
    {
        return self::generate($data, $secret);
    }
}
