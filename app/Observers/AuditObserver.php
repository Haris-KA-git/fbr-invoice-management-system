<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model)
    {
        $this->logActivity('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        $this->logActivity('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model)
    {
        $this->logActivity('deleted', $model, $model->getAttributes(), null);
    }

    protected function logActivity(string $action, Model $model, ?array $oldValues, ?array $newValues)
    {
        // Skip logging for AuditLog model to prevent infinite loops
        if ($model instanceof AuditLog) {
            return;
        }

        // Skip if no user is authenticated (e.g., during seeding)
        if (!auth()->check()) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}