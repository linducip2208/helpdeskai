<?php

namespace App\Services\Channels;

class TicketData
{
    public function __construct(
        public string $channel,
        public string $senderId,
        public string $senderName,
        public ?string $senderContact,
        public string $subject,
        public string $body,
        public ?string $externalMessageId = null,
        public ?string $replyToExternalId = null,
        public array $meta = [],
    ) {}
}
