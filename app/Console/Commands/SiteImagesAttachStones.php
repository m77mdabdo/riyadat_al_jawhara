<?php

namespace App\Console\Commands;

use App\Models\StoneType;
use App\Support\SiteImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Sets each stone type's catalogue cover from its chosen stock photo
 * (slot "stone-{slug}" in config/site-images.php) and records the credit on
 * the media item. The previous cover file is copied to the backup folder
 * (config site-images.backup_path) first, so the change can be undone by uploading
 * it again. Stones without a chosen photo keep their cover.
 */
class SiteImagesAttachStones extends Command
{
    protected $signature = 'site-images:attach-stones {--dry-run : Only list what would change}';

    protected $description = 'Use the chosen stock photos as stone catalogue covers, with credits';

    public function handle(): int
    {
        $choices = SiteImages::choices();
        $credits = [];

        foreach (StoneType::query()->orderBy('id')->get() as $stone) {
            $slot = 'stone-'.$stone->slug;
            $choice = $choices[$slot] ?? null;

            if (! $choice || ! SiteImages::hasFile($slot)) {
                $this->line("{$stone->slug}: no chosen photo, cover unchanged");

                continue;
            }

            $current = $stone->getFirstMedia('cover');
            if ($current?->getCustomProperty('stock_id') === $choice['id']) {
                $this->line("{$stone->slug}: already uses {$choice['id']}");
                $credits[] = [$stone, $choice];

                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("{$stone->slug}: would use {$choice['id']}");

                continue;
            }

            if ($current && is_file($current->getPath())) {
                $backup = config('site-images.backup_path')."/stone-{$stone->slug}-cover-{$current->id}-{$current->file_name}";
                File::ensureDirectoryExists(dirname($backup));
                File::copy($current->getPath(), $backup);
            }

            $stone->addMedia(SiteImages::path($slot))
                ->preservingOriginal()
                ->usingFileName("{$stone->slug}-{$choice['id']}.webp")
                ->withCustomProperties([
                    'stock_id' => $choice['id'],
                    'source' => $choice['source'],
                    'photographer' => $choice['photographer'],
                    'photographer_url' => $choice['photographer_url'],
                    'page_url' => $choice['page_url'],
                    'license_url' => $choice['license_url'],
                    'alt_ar' => $stone->name_ar,
                    'alt_en' => $stone->name_en,
                ])
                ->toMediaCollection('cover');

            $this->info("{$stone->slug}: cover set to {$choice['id']}");
            $credits[] = [$stone, $choice];
        }

        if (! $this->option('dry-run') && $credits !== []) {
            $this->writeCredits($credits);
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array{0: StoneType, 1: array<string, mixed>}>  $credits
     */
    private function writeCredits(array $credits): void
    {
        $path = config('site-images.credits_path');
        $existing = is_file($path) ? file_get_contents($path) : '';
        $head = str_contains($existing, '<!-- media-library -->')
            ? substr($existing, 0, strpos($existing, '<!-- media-library -->'))
            : $existing."\n";

        $rows = collect($credits)->map(fn (array $pair) => sprintf(
            '| %s (`%s`) | cover | %s | [%s](%s) | [%s](%s) | [licence](%s) |',
            $pair[0]->name_en,
            $pair[0]->slug,
            $pair[1]['source'],
            $pair[1]['photographer'],
            $pair[1]['photographer_url'],
            $pair[1]['id'],
            $pair[1]['page_url'],
            $pair[1]['license_url'],
        ))->implode("\n");

        File::put($path, $head."<!-- media-library -->\n## Media Library attachments\n\n"
            ."Stone catalogue covers set by `php artisan site-images:attach-stones` (credit also stored on each media item).\n"
            ."Earlier covers are copied to `storage/app/media-backup/` before being replaced.\n"
            ."Gallery images that came with the demo content have no recorded source; replace them with the company's own photos before launch.\n\n"
            ."| stone | collection | source | photographer | original | license link |\n|---|---|---|---|---|---|\n"
            .$rows."\n");
    }
}
