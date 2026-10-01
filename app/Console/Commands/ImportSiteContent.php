<?php

namespace App\Console\Commands;

use App\Actions\SiteContent\ImportSiteContent as Import;
use Illuminate\Console\Command;

/**
 * Run once after deploying the admin CMS (and safe to run again): creates the
 * editable menus, pages, image slots and permissions that don't exist yet,
 * matching what the site shows today. Never changes or deletes existing rows.
 */
class ImportSiteContent extends Command
{
    protected $signature = 'app:import-site-content';

    protected $description = 'Create missing editable website content (menus, pages, image slots, permissions) from the built-in defaults';

    public function handle(Import $import): int
    {
        foreach ($import->handle() as $kind => $count) {
            $this->line(sprintf('%-12s %d created', $kind, $count));
        }
        $this->info('Done. Existing rows were left unchanged.');

        return self::SUCCESS;
    }
}
