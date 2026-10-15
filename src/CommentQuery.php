<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\Traits\Comments\Caching;
use Pollora\Query\Traits\Comments\Fields;
use Pollora\Query\Traits\Comments\Filters;
use Pollora\Query\Traits\Comments\Order;
use Pollora\Query\Traits\Comments\PostFilters;
use Pollora\Query\Traits\Comments\Retrieval;
use Pollora\Query\Traits\Concerns\BuildsArguments;
use Pollora\Query\Traits\Concerns\LimitsResults;
use Pollora\Query\Traits\Concerns\PagedResults;
use Pollora\Query\Traits\DateQuery;
use Pollora\Query\Traits\MetaField;
use Pollora\Query\Traits\MetaQuery;
use WP_Comment_Query;

class CommentQuery
{
    use BuildsArguments, Caching, DateQuery, Fields, Filters, LimitsResults, MetaField, MetaQuery, Order, PagedResults, PostFilters, Retrieval;

    /**
     * @param  array<int>|int|null  $commentId  A comment ID, or a list of comment IDs.
     */
    public function __construct(array|int|null $commentId = null, ?string $fields = null)
    {
        if (is_int($commentId)) {
            $this->whereIn([$commentId]);
        } elseif (is_array($commentId)) {
            $this->whereIn($commentId);
        }

        if ($fields) {
            $this->fields($fields);
        }
    }

    /**
     * @param  array<int>|int|null  $commentId
     */
    public static function find(array|int|null $commentId = null): self
    {
        return new static($commentId);
    }

    public static function select(?string $fields = 'all'): self
    {
        return new static(null, $fields);
    }

    public function get(): WP_Comment_Query
    {
        return new WP_Comment_Query($this->getArguments());
    }
}
