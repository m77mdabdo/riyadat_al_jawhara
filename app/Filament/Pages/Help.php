<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * The admin guide (docs/ADMIN_GUIDE.md) inside the panel, in the admin's
 * language; opened from "Help" in the user menu.
 */
class Help extends Page
{
    protected static string $view = 'filament.pages.help';

    protected static bool $shouldRegisterNavigation = false;

    public const GUIDE = 'docs/ADMIN_GUIDE.md';

    /** Splits the guide: Arabic before this line, English after. */
    public const ENGLISH_MARKER = '<!-- english -->';

    public static function canAccess(): bool
    {
        return auth()->user()?->canAccessPanel(filament()->getPanel('admin')) ?? false;
    }

    public function getTitle(): string
    {
        return __('Help');
    }

    public function guide(): HtmlString
    {
        $path = base_path(self::GUIDE);
        $markdown = is_file($path) ? (string) file_get_contents($path) : '';
        [$arabic, $english] = array_pad(explode(self::ENGLISH_MARKER, $markdown, 2), 2, '');
        $section = app()->getLocale() === 'ar' ? $arabic : $english;

        // Screenshots are served to signed-in users by admin.help.image.
        $section = preg_replace_callback('~\]\(images/admin/([a-z0-9-]+\.webp)\)~', fn (array $m) => ']('.route('admin.help.image', $m[1]).')', $section);

        return new HtmlString(Str::markdown((string) $section, ['html_input' => 'strip', 'allow_unsafe_links' => false]));
    }
}
