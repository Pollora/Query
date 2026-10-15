<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

use Pollora\Query\QueryException;

/**
 * `orderby` as a map of field => direction, which WP_User_Query and WP_Comment_Query accept.
 */
trait OrdersByMap
{
    use ValidatesChoices;

    /**
     * @var array<string, string>|string|null
     */
    protected array|string|null $orderby = null;

    /**
     * Adds fields to sort by, in call order. Meta keys and meta query clause names are valid fields too.
     *
     * @param  array<string, string>|string  $field  A field, or a map of field => direction.
     *
     * @throws QueryException If a direction is not ASC or DESC.
     */
    public function orderBy(array|string $field, string $order = 'ASC'): self
    {
        $columns = is_array($field) ? $field : [$field => $order];
        $current = is_array($this->orderby) ? $this->orderby : [];

        foreach ($columns as $column => $direction) {
            $current[$column] = $this->assertDirection($direction);
        }

        $this->orderby = $current;

        return $this;
    }
}
