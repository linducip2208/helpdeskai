<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        object $target,
        ?string $label = null,
        ?array $meta = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => get_class($target),
            'target_id' => $target->id ?? null,
            'target_label' => $label ?? ($target->name ?? $target->title ?? $target->subject ?? null),
            'meta' => $meta,
            'ip_address' => Request::ip(),
        ]);
    }

    public static function logCustom(
        ?int $userId,
        string $action,
        string $targetType,
        ?int $targetId,
        ?string $targetLabel,
        ?array $meta = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_label' => $targetLabel,
            'meta' => $meta,
            'ip_address' => Request::ip(),
        ]);
    }
}
