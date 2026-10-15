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
     * @param  'meta'|'date'|'tax'  $type
     */
    protected function addSubQuery(string $type, callable|SubQueryAbstract $callback): void
    {
        $field = $type.'Query';
        $this->{$field} ??= ['relation' => 'AND'];

        if ($callback instanceof SubQueryAbstract) {
            $this->addSingleSubQuery($field, $callback);

            return;
        }

        // A fresh builder per call: reusing one would replay earlier clauses on every call.
        $class = '\\Pollora\\Query\\'.ucfirst($type).'Query';
        $builder = new $class;
        $callback($builder);
        $built = $builder->get();

        if (count($built) > 1) {
            $this->{$field} = array_merge($this->{$field}, $built);
        }
    }

    private function addSingleSubQuery(string $field, SubQueryAbstract $subQuery): void
    {
        $query = $subQuery->get();
        $state = $query['state'] ?? false;
        unset($query['state']);

        if ($state) {
            $this->{$field}[$state] = $query;
        } else {
            $this->{$field}[] = $query;
        }
    }
}
