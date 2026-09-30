<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Admin audit trail (spatie/laravel-activitylog): who created, changed or
 * deleted what, with the changed values only.
 *
 * By default every fillable attribute is logged. A model can narrow that with
 * `protected array $activityLogOnly = [...]` (leads, comments, users) and limit
 * events with `protected static array $recordEvents = [...]`. Personal data
 * captured by the public forms (IP, user agent) and counters bumped on public
 * page views are never logged.
 */
trait LogsAdminActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->useLogName('admin')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();

        return property_exists($this, 'activityLogOnly')
            ? $options->logOnly($this->activityLogOnly)
            : $options->logFillable()->logExcept(['ip_address', 'user_agent', 'views_count', 'password', 'remember_token']);
    }
}
