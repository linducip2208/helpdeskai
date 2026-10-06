<?php

namespace Database\Seeders;

use App\Models\AiFeatureConfig;
use Illuminate\Database\Seeder;

class AiFeatureConfigSeeder extends Seeder
{
    public function run(): void
    {
        $features = config('helpdesk.ai.features', []);

        foreach ($features as $key => $label) {
            AiFeatureConfig::firstOrCreate(
                ['feature_key' => $key],
                [
                    'provider_id' => null,
                    'model_id' => null,
                    'is_enabled' => false,
                    'options' => [],
                ]
            );
        }
    }
}
