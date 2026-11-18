<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Query;

use Pollora\Query\QueryBuilder\SubQuery as SubQueryAbstract;

/**
 * Shared behaviour of metaQuery(), dateQuery() and taxQuery(): the `<type>Query` property is filled
 * from a closure (given a fresh builder) or from a single sub-query.
 */
trait SubQuery
{
    /**
     * Adds a clause to a `<type>Query` and returns the updated query.
     *
     * The caller stores the result in its own property, so the trait never reads a property by name.
     *
     * @param  'meta'|'date'|'tax'  $type
     * @param  array<int|string, mixed>|null  $current  the query built so far, null when empty
     * @return array<int|string, mixed>
     */
    protected function mergeSubQuery(string $type, ?array $current, callable|SubQueryAbstract $callback): array
    {
        $current ??= ['relation' => 'AND'];

        if ($callback instanceof SubQueryAbstract) {
            return $this->mergeSingleSubQuery($current, $callback);
        }

        // A fresh builder per call: reusing one would replay earlier clauses on every call.
        $class = '\\Pollora\\Query\\'.ucfirst($type).'Query';
        $builder = new $class;
        $callback($builder);
        $built = $builder->get();

        if (count($built) > 1) {
            return array_merge($current, $built);
        }

        return $current;
    }

    /**
     * @param  array<int|string, mixed>  $current
     * @return array<int|string, mixed>
     */
    private function mergeSingleSubQuery(array $current, SubQueryAbstract $subQuery): array
    {
        $query = $subQuery->get();
        $state = $query['state'] ?? false;
        unset($query['state']);

        if ($state) {
            $current[$state] = $query;
        } else {
            $current[] = $query;
        }

        return $current;
    }
}
