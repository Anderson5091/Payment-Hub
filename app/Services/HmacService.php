<?php

namespace App\Services;

class HmacService
{
    public static function generate(array $data): string
    {
        ksort($data);
        $payload = implode('|', $data);
        return hash_hmac('sha256', $payload, config('app.hmac_secret'));
    }

    public static function verify(array $data, string $signature): bool
    {
        return hash_equals(self::generate($data), $signature);
    }

    public static function sign(array $data, string $secret): string
    {
        ksort($data);
        return hash_hmac('sha256', json_encode($data), $secret);
    }
}
