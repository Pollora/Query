<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryBuilder\SubQuery;
use Pollora\Query\Traits\Query\SubQuery as SubQueryTrait;

trait MetaQuery
{
    /**
     * @var array<string|array>
     */
    protected ?array $metaQuery = null;

    use SubQueryTrait {
        SubQueryTrait::query as genericQuery;
    }

    public function metaQuery(callable|SubQuery $callback): self
    {
        $this->initQuery('meta');

        return $this->genericQuery($callback);
    }
}
