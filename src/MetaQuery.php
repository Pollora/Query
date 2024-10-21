<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\QueryBuilder\MetaQueryBuilder;
use Pollora\Query\QueryBuilder\QueryBuilder;

class MetaQuery extends QueryBuilder
{
    public function meta(string $key): MetaQueryBuilder
    {
        return new MetaQueryBuilder($key);
    }
}
