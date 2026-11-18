<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryBuilder\SubQuery;
use Pollora\Query\Traits\Query\SubQuery as SubQueryTrait;

trait MetaQuery
{
    use SubQueryTrait;

    /**
     * @var array<string|array>
     */
    protected ?array $metaQuery = null;

    public function metaQuery(callable|SubQuery $callback): self
    {
        $this->metaQuery = $this->mergeSubQuery('meta', $this->metaQuery, $callback);

        return $this;
    }
}
