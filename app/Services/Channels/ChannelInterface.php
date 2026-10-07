<?php

namespace App\Services\Channels;

interface ChannelInterface
{
    public function name(): string;

    public function enabled(): bool;

    /**
     * Send a plain-text outbound message. Returns provider message id or null.
     */
    public function send(string $recipientId, string $text): ?string;
}
