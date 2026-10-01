<?php

namespace App\Models\Contracts;

/**
 * A record that can be opened on the public site ("View on site" in the admin).
 */
interface HasPublicUrl
{
    /** Localized public URL, or null when the record is not publicly visible. */
    public function publicUrl(?string $locale = null): ?string;
}
