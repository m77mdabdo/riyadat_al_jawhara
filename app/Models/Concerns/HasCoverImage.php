<?php

namespace App\Models\Concerns;

/**
 * URL of the model's "cover" image for social sharing / structured data.
 * Prefers the JPEG "medium" conversion (widest crawler support) and falls
 * back to the original upload when that conversion hasn't been generated.
 */
trait HasCoverImage
{
    public function coverImageUrl(string $conversion = 'medium'): ?string
    {
        $media = $this->getFirstMedia('cover');

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }
}
