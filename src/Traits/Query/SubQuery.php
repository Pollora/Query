<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Query;

use Pollora\Query\QueryBuilder\SubQuery as SubQueryAbstract;

trait SubQuery
{
    protected ?string $queryType = null;

    public function initQuery(string $type): self
    {
        $this->queryType = $type;

        return $this;
    }

    public function query(callable|SubQueryAbstract $callback): self
    {
        $this->initializeQueryField();
        $this->initializeQueryBuilder();

        if ($callback instanceof SubQueryAbstract) {
            $this->handleSubQueryCallback($callback);
        } else {
            $this->handleCallableCallback($callback);
        }

        return $this;
    }

    protected function initializeQueryField(): void
    {
        $queryField = $this->queryType.'Query';

        if ($this->{$queryField}) {
            return;
        }

        $this->{$queryField} = ['relation' => 'AND'];
    }

    protected function initializeQueryBuilder(): void
    {
        $class = '\\Pollora\\Query\\'.ucfirst($this->queryType).'Query';
        $this->queryBuilder[$this->queryType] = $this->queryBuilder[$this->queryType] ?? new $class();
    }

    protected function handleSubQueryCallback(SubQueryAbstract $callback): void
    {
        $queryField = $this->queryType.'Query';
        $subQuery = $this->getSubQueryState($callback);

        ['state' => $state, 'query' => $query] = $subQuery;

        if ($state) {
            $this->{$queryField}[$state] = $query;
        } else {
            $this->{$queryField} = array_merge($this->{$queryField}, [$query]);
        }
    }

    /**
     * @return array{
     *    state: string,
     *    query: array<string, int, bool>
     *  }
     * /
     */
    private function getSubQueryState(SubQueryAbstract $callback): array
    {
        $subQuery = $callback->get();

        $state = $subQuery['state'] ?? false;
        unset($subQuery['state']);

        return ['state' => $state, 'query' => $subQuery];
    }

    protected function handleCallableCallback(callable $callback): void
    {
        $queryField = $this->queryType.'Query';
        $callback($this->queryBuilder[$this->queryType]);
        $this->{$queryField} = array_merge($this->{$queryField}, $this->queryBuilder[$this->queryType]->get());
    }
}
