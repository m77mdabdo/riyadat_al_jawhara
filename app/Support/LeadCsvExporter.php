<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams leads as a UTF-8 CSV (with BOM so Excel shows Arabic correctly).
 * Cells that a spreadsheet would treat as a formula are prefixed with an
 * apostrophe (OWASP "CSV injection" guidance).
 */
class LeadCsvExporter
{
    public const HEADERS = [
        'ID', 'Received', 'Name', 'Phone', 'Email', 'City', 'Service', 'Stone type', 'Project',
        'Message', 'Status', 'Assigned to', 'Follow-up date', 'Source', 'Language', 'Consent at',
    ];

    public static function download(Builder $query, ?string $filename = null): StreamedResponse
    {
        $filename ??= 'leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS);

            $query->with(['service', 'stoneType', 'project', 'assignee'])
                ->orderBy('id')
                ->chunk(500, function ($leads) use ($out) {
                    foreach ($leads as $lead) {
                        fputcsv($out, array_map([self::class, 'safeCell'], self::row($lead)));
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<int, string|int|null>
     */
    public static function row(Lead $lead): array
    {
        return [
            $lead->id,
            $lead->created_at?->format('Y-m-d H:i'),
            $lead->name,
            $lead->phone,
            $lead->email,
            $lead->city,
            $lead->service?->name_en,
            $lead->stoneType?->name_en,
            $lead->project?->title_en,
            $lead->message,
            Lead::STATUSES[$lead->status] ?? $lead->status,
            $lead->assignee?->name,
            $lead->follow_up_at?->format('Y-m-d'),
            Lead::SOURCES[$lead->source] ?? $lead->source,
            $lead->locale,
            $lead->consented_at?->format('Y-m-d H:i'),
        ];
    }

    public static function safeCell(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
