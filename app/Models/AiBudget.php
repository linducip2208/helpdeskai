<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $scope
 * @property string|null $scope_id
 * @property string $period
 * @property float $limit_usd
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AiBudget extends Model
{
    public const SCOPES = ['global', 'provider', 'feature'];

    public const PERIODS = ['daily', 'monthly'];

    protected $fillable = [
        'scope',
        'scope_id',
        'period',
        'limit_usd',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'limit_usd' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function label(): string
    {
        $target = $this->scope === 'global' ? 'Global' : ($this->scope.':'.$this->scope_id);

        return "{$target} / {$this->period} — \${$this->limit_usd}";
    }
}
