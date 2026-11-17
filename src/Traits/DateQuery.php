<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryBuilder\SubQuery;
use Pollora\Query\Traits\Query\SubQuery as SubQueryTrait;

trait DateQuery
{
    use SubQueryTrait;

    /**
     * @var array<string|array>
     */
    protected ?array $dateQuery = null;

    public function dateQuery(callable|SubQuery $callback): self
    {
        $this->addSubQuery('date', $callback);

        return $this;
    }
}
