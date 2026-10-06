<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Models\AiProviderModel;

interface AiAdapterInterface
{
    public function send(AiProvider $provider, AiProviderModel $model, array $request): array;

    public function testConnection(AiProvider $provider): array;

    public function listModels(AiProvider $provider): array;
}
