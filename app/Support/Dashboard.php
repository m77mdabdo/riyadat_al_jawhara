<?php

namespace App\Support;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Project;
use App\Models\WhatsappClick;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Figures for the admin dashboard, cached for a few minutes so opening the
 * dashboard costs a handful of queries at most.
 */
class Dashboard
{
    public const TTL = 300;

    /**
     * @template T
     *
     * @param  Closure(): T  $build
     * @return T
     */
    private static function remember(string $key, Closure $build): mixed
    {
        return Cache::remember('dashboard:'.$key, self::TTL, $build);
    }

    /**
     * Leads per day for the last $days days (oldest first), optionally of one source.
     *
     * @return array<string, int> Y-m-d => count
     */
    public static function leadsPerDay(int $days, ?LeadSource $source = null): array
    {
        return self::remember("leads-per-day:{$days}:".($source->value ?? 'all'), function () use ($days, $source) {
            $from = Carbon::today()->subDays($days - 1);
            $counts = Lead::query()
                ->where('created_at', '>=', $from)
                ->when($source, fn ($query) => $query->where('source', $source->value))
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            $series = [];
            for ($date = $from->copy(); $date->lte(Carbon::today()); $date->addDay()) {
                $series[$date->toDateString()] = (int) ($counts[$date->toDateString()] ?? 0);
            }

            return $series;
        });
    }

    /**
     * Headline figures with the previous period for trends.
     *
     * @return array<string, int>
     */
    public static function kpis(): array
    {
        return self::remember('kpis', function () {
            $days = (int) config('site.dashboard_whatsapp_days');
            $week = Carbon::now()->subDays(7);

            return [
                'new' => Lead::query()->where('status', LeadStatus::New->value)->count(),
                'leads_week' => Lead::query()->where('created_at', '>=', $week)->count(),
                'leads_prev_week' => Lead::query()->whereBetween('created_at', [Carbon::now()->subDays(14), $week])->count(),
                'due' => Lead::followUpDue()->count(),
                'whatsapp' => WhatsappClick::query()->where('created_at', '>=', Carbon::now()->subDays($days))->count(),
                'whatsapp_prev' => WhatsappClick::query()->whereBetween('created_at', [Carbon::now()->subDays(2 * $days), Carbon::now()->subDays($days)])->count(),
                'whatsapp_leads' => Lead::query()->where('source', LeadSource::Whatsapp->value)->where('created_at', '>=', Carbon::now()->subDays($days))->count(),
                'total' => Lead::query()->count(),
                'projects' => Project::active()->count(),
            ];
        });
    }

    /**
     * @return array<int|string, int> service id (or "none") => leads, most first
     */
    public static function leadsByService(int $days = 90): array
    {
        return self::remember("leads-by-service:{$days}", fn () => Lead::query()
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->select('service_id', DB::raw('COUNT(*) as total'))
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'service_id')
            ->mapWithKeys(fn ($total, $id) => [$id ?: 'none' => (int) $total])
            ->all());
    }

    /**
     * @return array<int, int> stone type id => leads, top 5
     */
    public static function topStones(int $limit = 5): array
    {
        return self::remember("top-stones:{$limit}", fn () => Lead::query()
            ->whereNotNull('stone_type_id')
            ->select('stone_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('stone_type_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'stone_type_id')
            ->map(fn ($total) => (int) $total)
            ->all());
    }

    /** Percent change, or null without a previous figure. */
    public static function change(int $current, int $previous): ?int
    {
        return $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : null;
    }

    public static function flush(): void
    {
        foreach (['kpis', 'leads-by-service:90', 'top-stones:5', 'leads-per-day:14:all', 'leads-per-day:30:website', 'leads-per-day:30:whatsapp', 'content-health'] as $key) {
            Cache::forget('dashboard:'.$key);
        }
    }

    /**
     * Content problems worth fixing, with where to fix them.
     *
     * @return list<array{key: string, count: int, url: string}>
     */
    public static function contentHealth(): array
    {
        return self::remember('content-health', fn () => ContentHealth::checks());
    }
}
