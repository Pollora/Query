<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

use Closure;

class QueryBuilder
{
    /**
     * @var array<string|array>
     */
    protected array $query = ['relation' => 'AND'];

    /**
     * @var array<string|array>
     */
    protected array $stack = [];

    protected int $depth = 0;

    /**
     * @var array<string>
     */
    protected array $lastRelation = [];

    public function __construct()
    {
        $this->stack = [&$this->query];
    }

    public function where(Closure|SubQuery $callback): self
    {
        return $this->addCondition('AND', $callback);
    }

    public function andWhere(Closure|SubQuery $callback): self
    {
        return $this->addCondition('AND', $callback);
    }

    public function orWhere(Closure|SubQuery $callback): self
    {
        return $this->addCondition('OR', $callback);
    }

    protected function addCondition(string $relation, Closure|SubQuery $callback): self
    {
        $current = &$this->stack[$this->depth];
        $this->lastRelation[$this->depth] = $relation;

        $this->applyRelation($current, $relation);

        if ($callback instanceof SubQuery) {
            $this->handleSubQuery($current, $callback);
        } elseif (is_callable($callback)) {
            $this->handleCallback($current, $callback);
        }

        return $this;
    }

    /**
     * WP allows a single relation per level. When the relation changes on a level that already
     * holds several clauses, they are grouped first, so that chaining reads like Eloquent:
     * where(a)->where(b)->orWhere(c) is (a AND b) OR c.
     *
     * @param  array<string, string|int|array>  $current
     */
    private function applyRelation(array &$current, string $relation): void
    {
        $existing = array_diff_key($current, ['relation' => true]);

        if (count($existing) > 1 && ($current['relation'] ?? 'AND') !== $relation) {
            $current = ['relation' => $relation, ['relation' => $current['relation'] ?? 'AND'] + $existing];

            return;
        }

        $current['relation'] = $relation;
    }

    /**
     * @param  $current  array<string, string|int|array>
     */
    private function handleSubQuery(array &$current, SubQuery $subQueryInstance): void
    {
        $subQuery = $subQueryInstance->get();

        if (isset($subQuery['state']) && $subQuery['state']) {
            $state = $subQuery['state'];
            unset($subQuery['state']);
            $current[$state] = $subQuery;
        } else {
            unset($subQuery['state']);
            $current[] = $subQuery;
        }
    }

    /**
     * @param  $current  array<string, string|int|array>
     */
    private function handleCallback(array &$current, Closure $callback): void
    {
        $subQuery = ['relation' => 'AND'];
        $this->depth++;
        $this->stack[$this->depth] = &$subQuery;
        $callback($this);
        $this->depth--;

        $current[] = $subQuery;
    }

    /**
     * @return array<array|string>
     */
    public function get(): array
    {
        return $this->query;
    }
}
