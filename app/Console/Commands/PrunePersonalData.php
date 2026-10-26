<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Data retention: clears the IP address and user agent on comments and leads
 * older than the retention window. Rows are kept; only those two columns are
 * set to NULL. Scheduled daily in routes/console.php.
 */
class PrunePersonalData extends Command
{
    protected $signature = 'privacy:prune
                            {--days= : Override the retention window (default: config privacy.retention_days)}
                            {--dry-run : Only report how many rows would change}';

    protected $description = 'Remove IP addresses and user agents from comments and leads older than the retention window';

    private const TABLES = ['comments', 'leads'];

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('privacy.retention_days'));
        if ($days < 1) {
            $this->error('The retention window must be at least 1 day.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        foreach (self::TABLES as $table) {
            $query = DB::table($table)
                ->where('created_at', '<', $cutoff)
                ->where(fn ($q) => $q->whereNotNull('ip_address')->orWhereNotNull('user_agent'));

            $count = $this->option('dry-run')
                ? $query->count()
                : $query->update(['ip_address' => null, 'user_agent' => null]);

            $this->line(sprintf('%s: %d %s', $table, $count, $this->option('dry-run') ? 'would be cleared' : 'cleared'));
        }

        return self::SUCCESS;
    }
}
