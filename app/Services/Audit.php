<?php
namespace App\Services;

use App\Models\AuditLog;

class Audit
{
    public static function record(string $action, object|string|null $entity = null, ?string $description = null): void
    {
        $request = request();
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => is_object($entity) ? $entity::class : $entity,
            'entity_id' => is_object($entity) && isset($entity->id) ? $entity->id : null,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
