<?php
if (!defined('ABSPATH')) exit;

class Payment_Hub_HMAC {

    /**
     * Génère une signature HMAC SHA256
     */
    public static function sign(array $data, string $secret): string {

        ksort($data);

        // Ensure all values are scalar for implode
        $values = array_map(function($val) {
            return is_scalar($val) ? (string)$val : json_encode($val);
        }, $data);

        $payload = implode('|', $values);

        return hash_hmac('sha256', (string)$payload, (string)$secret);
    }

    /**
     * Vérifie une signature HMAC
     */
    public static function verify(array $data, string $secret, string $signature): bool {

        $expected = self::sign($data, $secret);

        return hash_equals($expected, $signature);
    }
}
