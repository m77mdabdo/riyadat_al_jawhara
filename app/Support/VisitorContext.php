<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * What we record about the visitor behind a public submission, read once from
 * the request so actions don't depend on the HTTP layer.
 */
final readonly class VisitorContext
{
    public function __construct(
        public ?string $ip,
        public ?string $userAgent,
        public ?string $referer,
        public string $locale,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ip: $request->ip(),
            userAgent: $request->userAgent() !== null ? Str::limit($request->userAgent(), 500, '') : null,
            referer: Str::limit((string) $request->headers->get('referer'), 2040, ''),
            locale: app()->getLocale(),
        );
    }
}
