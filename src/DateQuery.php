<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\QueryBuilder\DateQueryBuilder;
use Pollora\Query\QueryBuilder\QueryBuilder;

class DateQuery extends QueryBuilder
{
    private bool $inclusive = false;

    public function date(?string $key = null): DateQueryBuilder
    {
        return new DateQueryBuilder($key);
    }

    /**
     * Makes every date clause inclusive of its 'before'/'after' boundaries.
     *
     * WP_Date_Query only reads 'inclusive' on a clause (it is not inherited from the
     * query root), so the flag is applied to each clause when the query is built.
     */
    public function inclusive(): self
    {
        $this->inclusive = true;

        return $this;
    }

    /**
     * @return array<array|string>
     */
    public function get(): array
    {
        $query = parent::get();

        return $this->inclusive ? $this->applyInclusive($query) : $query;
    }

    /**
     * @param  array<array|string>  $query
     * @return array<array|string>
     */
    private function applyInclusive(array $query): array
    {
        foreach ($query as $key => $clause) {
            if (! is_array($clause)) {
                continue;
            }

            $query[$key] = isset($clause['column'])
                ? $clause + ['inclusive' => true]
                : $this->applyInclusive($clause);
        }

        return $query;
    }
}
