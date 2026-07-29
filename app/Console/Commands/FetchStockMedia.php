<?php

namespace App\Console\Commands;

use App\Services\StockMediaFetcher;
use Illuminate\Console\Command;

class FetchStockMedia extends Command
{
    protected $signature = 'fetch:stock-media
        {query : Search term, e.g. "marble texture"}
        {--source=pexels : pexels, pixabay, or unsplash}
        {--count=1 : how many results to download}
        {--video : fetch a video instead of a photo (pexels only)}';

    protected $description = 'Search Pexels/Pixabay/Unsplash for stock media and save optimized local copies';

    public function handle(StockMediaFetcher $fetcher): int
    {
        $query = $this->argument('query');
        $source = $this->option('source');
        $count = max(1, (int) $this->option('count'));

        $paths = $this->option('video')
            ? $fetcher->fetchVideos($query, $count)
            : $fetcher->fetchPhotos($query, $source, $count);

        if ($paths === []) {
            $this->error("No results for \"{$query}\" via {$source} (or the relevant API key is missing/invalid).");

            return self::FAILURE;
        }

        foreach ($paths as $path) {
            $sizeKb = round(filesize($path) / 1024);
            $this->info("Saved: {$path} ({$sizeKb} KB)");
        }

        return self::SUCCESS;
    }
}
