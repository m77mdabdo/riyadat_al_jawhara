<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\Storage;

/**
 * Extra content blocks of a page (Website Content → Pages → Content blocks),
 * prepared for display in the current language: rich text is sanitized,
 * image paths become URLs and links are localized.
 */
class PageBlocks
{
    public const TYPES = ['rich_text', 'image_text', 'gallery', 'cta', 'quote'];

    public const DISK = 'public';

    public const DIRECTORY = 'pages';

    /**
     * @return list<array<string, mixed>>
     */
    public static function for(?Page $page): array
    {
        $blocks = [];
        foreach ($page->blocks ?? [] as $block) {
            $type = $block['type'] ?? null;
            $data = (array) ($block['data'] ?? []);
            if (! in_array($type, self::TYPES, true)) {
                continue;
            }
            $prepared = self::prepare($type, $data);
            if ($prepared !== null) {
                $blocks[] = ['type' => $type] + $prepared;
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private static function prepare(string $type, array $data): ?array
    {
        $text = fn (string $field) => self::localized($data, $field);

        return match ($type) {
            'rich_text' => filled($body = $text('body')) ? ['html' => str($body)->sanitizeHtml()->toString()] : null,
            'image_text' => filled($text('title')) || filled($text('text')) ? [
                'title' => $text('title'),
                'html' => filled($body = $text('text')) ? str($body)->sanitizeHtml()->toString() : '',
                'image' => self::url($data['image'] ?? null),
                'alt' => $text('alt'),
                'image_end' => ($data['image_position'] ?? 'start') === 'end',
            ] : null,
            'gallery' => ($images = array_values(array_filter(array_map(fn ($path) => self::url($path), (array) ($data['images'] ?? []))))) !== [] ? [
                'title' => $text('title'),
                'images' => $images,
            ] : null,
            'cta' => filled($text('title')) ? [
                'title' => $text('title'),
                'text' => $text('text'),
                'label' => $text('button_label') ?: __('site.common.request_quote'),
                'url' => filled($data['button_link'] ?? null) ? LocalizedUrl::link((string) $data['button_link']) : route('contact'),
            ] : null,
            'quote' => filled($text('quote')) ? ['quote' => $text('quote'), 'author' => $text('author')] : null,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function localized(array $data, string $field): string
    {
        [$primary, $fallback] = app()->getLocale() === 'ar' ? ['ar', 'en'] : ['en', 'ar'];

        return trim((string) (($data["{$field}_{$primary}"] ?? null) ?: ($data["{$field}_{$fallback}"] ?? '')));
    }

    private static function url(mixed $path): ?string
    {
        return is_string($path) && $path !== '' && ! str_contains($path, '..') ? Storage::disk(self::DISK)->url($path) : null;
    }
}
