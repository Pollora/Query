<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\Traits\Concerns\BuildsArguments;
use Pollora\Query\Traits\Concerns\LimitsResults;
use Pollora\Query\Traits\Concerns\PagedResults;
use Pollora\Query\Traits\DateQuery;
use Pollora\Query\Traits\MetaField;
use Pollora\Query\Traits\MetaQuery;
use Pollora\Query\Traits\User\Fields;
use Pollora\Query\Traits\User\Filters;
use Pollora\Query\Traits\User\Order;
use Pollora\Query\Traits\User\Performance;
use Pollora\Query\Traits\User\Retrieval;
use WP_User_Query;

class UserQuery
{
    use BuildsArguments, DateQuery, Fields, Filters, LimitsResults, MetaField, MetaQuery, Order, PagedResults, Performance, Retrieval;

    /**
     * @param  array<int>|int|null  $userId  A user ID, or a list of user IDs.
     * @param  string|array<string>|null  $fields
     */
    public function __construct(array|int|null $userId = null, string|array|null $fields = null)
    {
        if (is_int($userId)) {
            $this->whereIn([$userId]);
        } elseif (is_array($userId)) {
            $this->whereIn($userId);
        }

        if ($fields) {
            $this->fields($fields);
        }
    }

    /**
     * @param  array<int>|int|null  $userId
     */
    public static function find(array|int|null $userId = null): self
    {
        return new static($userId);
    }

    /**
     * @param  string|array<string>|null  $fields
     */
    public static function select(string|array|null $fields = 'all'): self
    {
        return new static(null, $fields);
    }

    public function get(): WP_User_Query
    {
        return new WP_User_Query($this->getArguments());
    }
}
