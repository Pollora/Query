<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\QueryBuilder\DateQueryBuilder;
use Pollora\Query\QueryBuilder\MetaQueryBuilder;

class Query
{
    public static function meta(string $key): MetaQueryBuilder
    {
        return new MetaQueryBuilder($key);
    }

    public static function date(string $key): DateQueryBuilder
    {
        return new DateQueryBuilder($key);
    }
}
