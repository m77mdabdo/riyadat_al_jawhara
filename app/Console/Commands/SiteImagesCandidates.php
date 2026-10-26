<?php

namespace App\Console\Commands;

use App\Services\StockCandidateFinder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Downloads 4-6 stock candidates per static image slot into
 * storage/app/stock-candidates/{slot}/ (git-ignored) and scores them.
 */
class SiteImagesCandidates extends Command
{
    protected $signature = 'site-images:candidates
                            {slot?* : Slot keys from config/site-images.php (default: all)}
                            {--count=5 : Pexels results per slot (Pixabay adds about half as many)}';

    protected $description = 'Fetch and score stock-photo candidates for the static image slots';

    public function handle(StockCandidateFinder $finder): int
    {
        $slots = config('site-images.slots');
        $selected = $this->argument('slot') ?: array_keys($slots);

        foreach ($selected as $slot) {
            if (! isset($slots[$slot])) {
                $this->error("Unknown slot: {$slot}");

                return self::FAILURE;
            }

            $config = $slots[$slot];
            $dir = storage_path("app/stock-candidates/{$slot}");
            File::ensureDirectoryExists($dir);

            $found = $finder->search($config['query'], (int) $this->option('count'));
            $kept = [];

            foreach ($found as $candidate) {
                $path = $dir.'/'.$candidate['id'].'.webp';
                $maxWidth = $config['max_width'] ?? 1920;
                $fits = fn () => is_file($path) && filesize($path) <= $config['max_kb'] * 1024 && getimagesize($path)[0] <= $maxWidth;

                // Re-download files from earlier runs that don't meet the current budget.
                if (! $fits() && ! $finder->download($candidate, $path, $maxWidth, $config['max_kb'])) {
                    continue;
                }

                [$w, $h] = getimagesize($path);
                $kept[] = $candidate + $finder->score($path, $candidate, $config['text_space']) + [
                    'file' => "{$slot}/{$candidate['id']}.webp",
                    'kb' => (int) round(filesize($path) / 1024),
                    'saved_width' => $w,
                    'saved_height' => $h,
                ];
            }

            usort($kept, fn ($a, $b) => $b['score'] <=> $a['score']);
            File::put($dir.'/candidates.json', json_encode($kept, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            $this->info(sprintf('%-18s %d candidates%s', $slot, count($kept), $kept ? ' (best: '.$kept[0]['id'].')' : ''));
        }

        return self::SUCCESS;
    }
}
