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

        if ($callback instanceof SubQuery) {
            $this->handleSubQuery($current, $callback);
        } elseif (is_callable($callback)) {
            $this->handleCallback($current, $callback);
        }

        $current['relation'] = $this->lastRelation[$this->depth] ?? 'AND';

        return $this;
    }

    /**
     * @param $current array<string, string|int|array>
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
     * @param $current array<string, string|int|array>
     */
    private function handleCallback(array &$current, Closure $callback): void
    {
        $subQuery = ['relation' => 'AND'];
        $this->depth++;
        $this->stack[$this->depth] = &$subQuery;
        $callback($this);
        $this->depth--;

        $subQuery['relation'] = $this->lastRelation[$this->depth + 1] ?? 'AND';
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
