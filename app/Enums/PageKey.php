<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasLabel;

/**
 * The fixed public pages whose header, extra blocks and SEO are editable
 * (Website Content → Pages). Header texts are site texts; their keys are
 * listed here so the page form edits the same text as Site texts.
 */
enum PageKey: string implements HasLabel
{
    use HasLabelOptions;

    case About = 'about';
    case Contact = 'contact';
    case Services = 'services';
    case Stones = 'stones';
    case Projects = 'projects';
    case Gallery = 'gallery';
    case Blog = 'blog';
    case Faq = 'faq';
    case Search = 'search';
    case Thanks = 'thanks';
    case NotFound = 'not-found';
    case Privacy = 'privacy';
    case Terms = 'terms';

    public function getLabel(?string $locale = null): string
    {
        return __('enums.page.'.$this->value, [], $locale);
    }

    /** Named route of the page, or null (404 has no address of its own). */
    public function route(): ?string
    {
        return match ($this) {
            self::About => 'about',
            self::Contact => 'contact',
            self::Services => 'services.index',
            self::Stones => 'stones.index',
            self::Projects => 'projects.index',
            self::Gallery => 'gallery',
            self::Blog => 'blog.index',
            self::Faq => 'faq',
            self::Search => 'search',
            self::Thanks => 'contact.thanks',
            self::NotFound => null,
            self::Privacy => 'legal.privacy',
            self::Terms => 'legal.terms',
        };
    }

    public function titleKey(): string
    {
        return match ($this) {
            self::About => 'site.about.title',
            self::Contact => 'site.contact.title',
            self::Services => 'site.services.title',
            self::Stones => 'site.stones.title',
            self::Projects => 'site.projects.title',
            self::Gallery => 'site.gallery.title',
            self::Blog => 'site.blog.title',
            self::Faq => 'site.faq.title',
            self::Search => 'site.search.title',
            self::Thanks => 'site.contact.thanks_heading',
            self::NotFound => 'site.errors.404_heading',
            self::Privacy => 'site.legal.privacy_policy_title',
            self::Terms => 'site.legal.terms_title',
        };
    }

    public function subtitleKey(): string
    {
        return match ($this) {
            self::About => 'site.about.subtitle',
            self::Contact => 'site.contact.subtitle',
            self::Services => 'site.services.meta_description',
            self::Stones => 'site.stones.meta_description',
            self::Projects => 'site.projects.meta_description',
            self::Gallery => 'site.gallery.subtitle',
            self::Blog => 'site.blog.subtitle',
            self::Faq => 'site.faq.subtitle',
            self::Search => 'site.search.subtitle',
            self::Thanks => 'site.contact.success_message',
            self::NotFound => 'site.errors.404_message',
            self::Privacy => 'site.legal.privacy_subtitle',
            self::Terms => 'site.legal.terms_subtitle',
        };
    }

    /** Site-image slot used as the header band when the page has no banner. */
    public function texture(): string
    {
        return match ($this) {
            self::About, self::Services, self::Blog, self::Privacy, self::Terms, self::NotFound => 'texture-marble',
            self::Contact, self::Projects, self::Thanks => 'texture-sandstone',
            self::Stones, self::Faq, self::Search => 'texture-quartz',
            self::Gallery => 'texture-granite',
        };
    }

    /** Pages that are never indexed anyway (noindex is fixed). */
    public function alwaysNoindex(): bool
    {
        return in_array($this, [self::Search, self::Thanks, self::NotFound], true);
    }
}
