<?php

namespace App\Services;

class TotpService
{
    private const PERIOD = 30;

    private const DIGITS = 6;

    private const ALGO = 'sha1';

    private const B32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function currentCode(string $secret, ?int $timestamp = null): string
    {
        return $this->codeAt($secret, (int) floor(($timestamp ?? time()) / self::PERIOD));
    }

    public function verify(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $counter = (int) floor(($timestamp ?? time()) / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->codeAt($secret, $counter + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    public function otpAuthUrl(string $secret, string $account, string $issuer): string
    {
        $label = rawurlencode($issuer).':'.rawurlencode($account);

        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    public function qrUrl(string $otpauth, int $size = 220): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?'.http_build_query([
            'size' => "{$size}x{$size}",
            'data' => $otpauth,
        ]);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => $this->randomCode())
            ->all();
    }

    private function randomCode(): string
    {
        $part = fn () => strtolower(bin2hex(random_bytes(5)));

        return $part().'-'.$part();
    }

    private function codeAt(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binCounter = pack('N*', 0).pack('N*', $counter);

        $hash = hash_hmac(self::ALGO, $binCounter, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';
        for ($i = 0, $n = strlen($bytes); $i < $n; $i++) {
            $bits .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($bits, 5);
        $out = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $out .= self::B32_ALPHABET[bindec($chunk)];
        }

        return $out;
    }

    private function base32Decode(string $b32): string
    {
        $b32 = strtoupper(rtrim($b32, '='));
        $bits = '';
        foreach (str_split($b32) as $char) {
            $pos = strpos(self::B32_ALPHABET, $char);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr(bindec($byte));
            }
        }

        return $bytes;
    }
}
