<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

use WP_Term_Query;

/**
 * Terminal methods that run the query with the arguments that fit their purpose.
 *
 * They work on a copy: the builder keeps its own arguments, so it can still be reused afterwards.
 */
trait Retrieval
{
    /**
     * The first matching term (a WP_Term), or null. With `fields()` set, the first value of that kind.
     */
    public function first(): object|string|int|null
    {
        $terms = $this->runOnCopy(function (self $copy): void {
            $copy->number = 1;
            $copy->offset = null;
            $copy->guardCountField();
        });

        return $terms[0] ?? (array_values($terms)[0] ?? null);
    }

    /**
     * @return array<int>
     */
    public function ids(): array
    {
        return array_map('intval', array_values($this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
        })));
    }

    /**
     * @return array<string>
     */
    public function names(): array
    {
        return array_values($this->runOnCopy(function (self $copy): void {
            $copy->fields = 'names';
        }));
    }

    /**
     * @return array<string>
     */
    public function slugs(): array
    {
        return array_values($this->runOnCopy(function (self $copy): void {
            $copy->fields = 'slugs';
        }));
    }

    /**
     * The total number of matching terms, whatever the limit or offset set on the builder.
     */
    public function count(): int
    {
        $query = new WP_Term_Query($this->copyWith(function (self $copy): void {
            $copy->fields = 'count';
            $copy->number = null;
            $copy->offset = null;
        }));

        return (int) $query->terms;
    }

    public function exists(): bool
    {
        return $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
            $copy->number = 1;
            $copy->offset = null;
        }) !== [];
    }

    /**
     * @return array<mixed>
     */
    private function runOnCopy(callable $configure): array
    {
        $query = new WP_Term_Query($this->copyWith($configure));

        return is_array($query->terms) ? $query->terms : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function copyWith(callable $configure): array
    {
        $copy = clone $this;
        $configure($copy);

        return $copy->getArguments();
    }

    private function guardCountField(): void
    {
        // A 'count' query returns a number, there is no "first" term to pick from it.
        if ($this->fields === 'count') {
            $this->fields = 'all';
        }
    }
}
