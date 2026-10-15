<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\Traits\Concerns\RetrievesRecords;
use WP_Comment_Query;

trait Retrieval
{
    use RetrievesRecords;

    protected function newWpQuery(): object
    {
        return new WP_Comment_Query;
    }

    /**
     * Descendants are ignored by WP for ids and counts, and would hide the record a terminal asks for.
     */
    protected function prepareCopy(self $copy): void
    {
        $copy->hierarchical = null;
    }
}
