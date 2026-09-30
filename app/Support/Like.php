<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * "Contains" searches with the visitor's % and _ taken literally.
 *
 * Uses an explicit ESCAPE character so it behaves the same on MySQL (whose
 * default escape is a backslash) and SQLite (which has no default escape).
 */
final class Like
{
    public const ESCAPE = '!';

    public static function contains(string $term): string
    {
        return '%'.str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE.self::ESCAPE, self::ESCAPE.'%', self::ESCAPE.'_'], $term).'%';
    }

    /**
     * Adds "(col1 LIKE ? OR col2 LIKE ? ...)" for the term.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @return Builder<TModel>
     */
    public static function anyColumn(Builder $query, array $columns, string $term): Builder
    {
        $pattern = self::contains($term);
        $grammar = $query->getQuery()->getGrammar();

        return $query->where(function (Builder $q) use ($columns, $pattern, $grammar) {
            foreach ($columns as $column) {
                $q->orWhereRaw($grammar->wrap($column)." like ? escape '".self::ESCAPE."'", [$pattern]);
            }
        });
    }
}
