<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WebPushService
{
    public function generateVapidKeys(): array
    {
        $pkey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if (! $pkey) {
            throw new RuntimeException('Failed to generate EC keypair: ' . openssl_error_string());
        }

        $details = openssl_pkey_get_details($pkey);
        $publicRaw = "\x04" . $details['ec']['x'] . $details['ec']['y'];

        openssl_pkey_export($pkey, $privPem);

        return [
            'public' => $this->b64url($publicRaw),
            'private_pem' => $privPem,
        ];
    }

    public function send(PushSubscription $subscription, array $payload): bool
    {
        $body = json_encode($payload);
        $endpoint = $subscription->endpoint;
        $audience = $this->originOf($endpoint);

        $publicKey = config('webpush.vapid.public_key');
        $privatePem = config('webpush.vapid.private_key');

        if (! $publicKey || ! $privatePem) {
            Log::warning('Web push not sent — VAPID keys missing. Run php artisan webpush:vapid-keys');
            return false;
        }

        $jwt = $this->createVapidJwt($audience, $privatePem);

        $encrypted = $this->encryptPayload(
            $body,
            $this->b64urlDecode($subscription->p256dh),
            $this->b64urlDecode($subscription->auth),
        );

        $headers = [
            'Authorization' => 'vapid t=' . $jwt . ', k=' . $publicKey,
            'Content-Type' => 'application/octet-stream',
            'Content-Encoding' => 'aes128gcm',
            'TTL' => (string) config('webpush.ttl', 86400),
        ];

        try {
            $response = Http::withHeaders($headers)
                ->withBody($encrypted, 'application/octet-stream')
                ->post($endpoint);

            if ($response->status() === 404 || $response->status() === 410) {
                $subscription->delete();
                return false;
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Web push send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function createVapidJwt(string $audience, string $privatePem): string
    {
        $header = $this->b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));

        $payload = $this->b64url(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200,
            'sub' => config('webpush.vapid.subject', 'mailto:admin@example.com'),
        ]));

        $message = $header . '.' . $payload;

        openssl_sign($message, $derSignature, $privatePem, OPENSSL_ALGO_SHA256);
        $joseSig = $this->derToJose($derSignature);

        return $message . '.' . $this->b64url($joseSig);
    }

    private function encryptPayload(string $payload, string $userPublic, string $authSecret): string
    {
        $local = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $localDetails = openssl_pkey_get_details($local);
        $localPublic = "\x04" . $localDetails['ec']['x'] . $localDetails['ec']['y'];

        $userPubPem = $this->rawPublicKeyToPem($userPublic);
        $sharedSecret = openssl_pkey_derive($userPubPem, $local);

        $salt = random_bytes(16);

        $keyInfo = "WebPush: info\x00" . $userPublic . $localPublic;
        $ikm = hash_hkdf('sha256', $sharedSecret, 32, $keyInfo, $authSecret);

        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\x00", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\x00", $salt);

        $padded = $payload . "\x02";

        $tag = '';
        $ciphertext = openssl_encrypt($padded, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);

        $rs = 4096;
        $header = $salt . pack('N', $rs) . chr(strlen($localPublic)) . $localPublic;

        return $header . $ciphertext . $tag;
    }

    private function originOf(string $url): string
    {
        $parts = parse_url($url);
        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    private function b64url(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $str): string
    {
        $pad = 4 - (strlen($str) % 4);
        if ($pad < 4) {
            $str .= str_repeat('=', $pad);
        }
        return base64_decode(strtr($str, '-_', '+/'));
    }

    private function rawPublicKeyToPem(string $raw): string
    {
        $der = "\x30\x59\x30\x13\x06\x07\x2A\x86\x48\xCE\x3D\x02\x01"
            . "\x06\x08\x2A\x86\x48\xCE\x3D\x03\x01\x07\x03\x42\x00" . $raw;

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    private function derToJose(string $der): string
    {
        $offset = 4;
        $rLen = ord($der[3]);
        $r = substr($der, $offset, $rLen);
        $sLen = ord($der[$offset + $rLen + 1]);
        $s = substr($der, $offset + $rLen + 2, $sLen);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }
}
