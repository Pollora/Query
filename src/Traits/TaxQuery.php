<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryBuilder\SubQuery;
use Pollora\Query\Traits\Query\SubQuery as SubQueryTrait;

trait TaxQuery
{
    use SubQueryTrait;

    /**
     * @var array<int|string, mixed>|null
     */
    protected ?array $taxQuery = null;

    public function taxQuery(callable|SubQuery $callback): self
    {
        $this->taxQuery = $this->mergeSubQuery('tax', $this->taxQuery, $callback);

        return $this;
    }
}
