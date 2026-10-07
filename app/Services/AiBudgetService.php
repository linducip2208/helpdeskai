<?php

namespace App\Services;

use App\Models\AiBudget;
use App\Models\AiUsageLog;
use Carbon\Carbon;

class AiBudgetService
{
    /**
     * Check whether a dispatch may proceed under all active budgets.
     *
     * @return array{allowed: bool, blocking: ?AiBudget, spent: float}
     */
    public function check(string $featureKey, ?int $providerId = null): array
    {
        $budgets = AiBudget::where('is_active', true)
            ->where(function ($q) use ($featureKey, $providerId) {
                $q->where('scope', 'global')
                    ->orWhere(function ($q) use ($featureKey) {
                        $q->where('scope', 'feature')->where('scope_id', $featureKey);
                    });

                if ($providerId) {
                    $q->orWhere(function ($q) use ($providerId) {
                        $q->where('scope', 'provider')->where('scope_id', (string) $providerId);
                    });
                }
            })
            ->get();

        foreach ($budgets as $budget) {
            $spent = $this->spent($budget, $featureKey, $providerId);

            if ($spent >= (float) $budget->limit_usd) {
                return ['allowed' => false, 'blocking' => $budget, 'spent' => $spent];
            }
        }

        return ['allowed' => true, 'blocking' => null, 'spent' => 0.0];
    }

    public function spent(AiBudget $budget, ?string $featureKey = null, ?int $providerId = null): float
    {
        $since = $budget->period === 'daily'
            ? Carbon::now()->startOfDay()
            : Carbon::now()->startOfMonth();

        $query = AiUsageLog::where('created_at', '>=', $since)
            ->where('success', true);

        if ($budget->scope === 'feature' && $budget->scope_id) {
            $query->where('feature_key', $budget->scope_id);
        } elseif ($budget->scope === 'provider' && $budget->scope_id) {
            $query->where('provider_id', $budget->scope_id);
        } elseif ($featureKey !== null && $budget->scope === 'global') {
            // global scope counts everything; keep query unfiltered
        }

        if ($providerId && $budget->scope === 'global') {
            // global counts everything regardless of provider
        }

        return round((float) $query->sum('cost_estimated'), 4);
    }

    /**
     * @return array<string, array{spent: float, limit: ?float, remaining: ?float}>
     */
    public function summary(): array
    {
        $out = [];

        foreach (AiBudget::where('is_active', true)->get() as $budget) {
            $spent = $this->spent($budget);
            $limit = (float) $budget->limit_usd;
            $out[$budget->label()] = [
                'spent' => $spent,
                'limit' => $limit,
                'remaining' => round(max(0, $limit - $spent), 4),
            ];
        }

        return $out;
    }
}
