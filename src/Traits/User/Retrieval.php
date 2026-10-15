<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

use Pollora\Query\QueryException;
use WP_User_Query;

/**
 * Terminal methods that run the query with the arguments that fit their purpose.
 *
 * They work on a copy: the builder keeps its own arguments, so it can still be reused afterwards.
 */
trait Retrieval
{
    /**
     * The first matching user (a WP_User), or null. With `fields()` set, the first value of that kind.
     */
    public function first(): object|string|int|null
    {
        $results = $this->runOnCopy(function (self $copy): void {
            $copy->number = 1;
            $copy->offset = null;
            $copy->paged = null;
            $copy->countTotal = false;
        });

        return $results[0] ?? null;
    }

    /**
     * @return array<int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ID';
            $copy->countTotal = false;
        }));
    }

    /**
     * The values of a single field, e.g. `pluck('user_email')`.
     *
     * @return array<string>
     */
    public function pluck(string $field): array
    {
        if (in_array($field, ['all', '*'], true)) {
            throw new QueryException("pluck() expects a single field, 'all' returns whole users: use get().");
        }

        return $this->runOnCopy(function (self $copy) use ($field): void {
            $copy->fields($field);
            $copy->countTotal = false;
        });
    }

    /**
     * The total number of matching users, whatever the limit, offset or page set on the builder.
     */
    public function count(): int
    {
        $query = new WP_User_Query($this->copyWith(function (self $copy): void {
            $copy->fields = 'ID';
            $copy->number = 1;
            $copy->offset = null;
            $copy->paged = null;
            $copy->countTotal = true;
        }));

        return (int) $query->get_total();
    }

    public function exists(): bool
    {
        return $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ID';
            $copy->number = 1;
            $copy->offset = null;
            $copy->paged = null;
            $copy->countTotal = false;
        }) !== [];
    }

    /**
     * @return array<mixed>
     */
    private function runOnCopy(callable $configure): array
    {
        $query = new WP_User_Query($this->copyWith($configure));

        return array_values($query->get_results());
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
}
