<?php

namespace App\Services;

class HmacService
{
    public static function generate(array $data, ?string $secret = null): string
    {
        $secret = (string)($secret ?? config('app.payment_hub_secret'));
        ksort($data);
        
        // Ensure all values are scalar for implode
        $values = array_map(function($val) {
            return is_scalar($val) ? (string)$val : json_encode($val);
        }, $data);

        $payload = implode('|', $values);
        return hash_hmac('sha256', (string)$payload, $secret);
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
