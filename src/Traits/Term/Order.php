<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

use Pollora\Query\QueryException;

trait Order
{
    protected ?string $orderby = null;

    protected ?string $order = null;

    /**
     * Terms are sorted by a single field: a term field, 'count', 'include', 'slug__in',
     * 'meta_value', 'meta_value_num', a meta key / meta query clause name, or 'none'.
     *
     * @throws QueryException If the direction is not ASC or DESC.
     */
    public function orderBy(string $field, string $order = 'ASC'): self
    {
        $order = strtoupper($order);

        if (! in_array($order, ['ASC', 'DESC'], true)) {
            throw new QueryException('Invalid order direction supplied: '.$order);
        }

        $this->orderby = $field;
        $this->order = $order;

        return $this;
    }

    public function orderByName(string $order = 'ASC'): self
    {
        return $this->orderBy('name', $order);
    }

    public function orderByCount(string $order = 'DESC'): self
    {
        return $this->orderBy('count', $order);
    }

    /**
     * Keeps the order of the IDs given to whereIn(). WP ignores it when whereIn() is empty.
     */
    public function orderByInclude(): self
    {
        return $this->orderBy('include');
    }

    /**
     * Keeps the order of the slugs given to slug(). WP ignores it unless slug() received an array.
     */
    public function orderBySlugIn(): self
    {
        return $this->orderBy('slug__in');
    }

    public function withoutOrder(): self
    {
        return $this->orderBy('none');
    }
}
