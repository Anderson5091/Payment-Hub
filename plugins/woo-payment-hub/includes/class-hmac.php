<?php
if (!defined('ABSPATH')) exit;

class Payment_Hub_HMAC {

    /**
     * Génère une signature HMAC SHA256
     */
    public static function sign(array $data, string $secret): string {

        ksort($data);

        $payload = implode('|', $data);

        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Vérifie une signature HMAC
     */
    public static function verify(array $data, string $secret, string $signature): bool {

        $expected = self::sign($data, $secret);

        return hash_equals($expected, $signature);
    }
}
