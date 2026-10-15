<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Networks;

use Pollora\Query\Traits\Concerns\RetrievesRecords;
use WP_Network_Query;

trait Retrieval
{
    use RetrievesRecords;

    protected function newWpQuery(): object
    {
        return new WP_Network_Query;
    }
}
