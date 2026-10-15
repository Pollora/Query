<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\Traits\Concerns\BuildsArguments;
use Pollora\Query\Traits\Concerns\LimitsResults;
use Pollora\Query\Traits\MetaField;
use Pollora\Query\Traits\MetaQuery;
use Pollora\Query\Traits\Term\Caching;
use Pollora\Query\Traits\Term\Fields;
use Pollora\Query\Traits\Term\Filters;
use Pollora\Query\Traits\Term\Hierarchy;
use Pollora\Query\Traits\Term\Order;
use Pollora\Query\Traits\Term\Retrieval;
use WP_Term_Query;

class TermQuery
{
    use BuildsArguments, Caching, Fields, Filters, Hierarchy, LimitsResults, MetaField, MetaQuery, Order, Retrieval;

    /**
     * @param  array<int>|int|null  $termId  A term ID, or a list of term IDs.
     */
    public function __construct(array|int|null $termId = null, ?string $fields = null)
    {
        if (is_int($termId)) {
            $this->whereIn([$termId]);
        } elseif (is_array($termId)) {
            $this->whereIn($termId);
        }

        if ($fields) {
            $this->fields($fields);
        }
    }

    /**
     * @param  array<int>|int|null  $termId
     */
    public static function find(array|int|null $termId = null): self
    {
        return new static($termId);
    }

    public static function select(?string $fields = 'all'): self
    {
        return new static(null, $fields);
    }

    public function get(): WP_Term_Query
    {
        return new WP_Term_Query($this->getArguments());
    }
}
