<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\QueryBuilder\QueryBuilder;
use Pollora\Query\QueryBuilder\TaxQueryBuilder;

class TaxQuery extends QueryBuilder
{
    public function taxonomy(string $taxonomy): TaxQueryBuilder
    {
        return new TaxQueryBuilder($taxonomy);
    }
}
