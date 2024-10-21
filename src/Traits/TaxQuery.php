<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryBuilder\SubQuery;
use Pollora\Query\Traits\Query\SubQuery as SubQueryTrait;

trait TaxQuery
{
    /**
     * @var array<string|array>
     */
    protected ?array $taxQuery = null;

    use SubQueryTrait {
        SubQueryTrait::query as genericQuery;
    }

    public function taxQuery(callable|SubQuery $callback): self
    {
        $this->initQuery('tax');

        return $this->genericQuery($callback);
    }
}
