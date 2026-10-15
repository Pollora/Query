<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\Traits\Concerns\DisablesOrder;
use Pollora\Query\Traits\Concerns\OrdersByMap;

trait Order
{
    use DisablesOrder, OrdersByMap;

    public function orderByDate(string $order = 'DESC'): self
    {
        return $this->orderBy('comment_date_gmt', $order);
    }

    public function orderByAuthor(string $order = 'ASC'): self
    {
        return $this->orderBy('comment_author', $order);
    }

    /**
     * Keeps the order of the IDs given to whereIn().
     */
    public function orderByInclude(): self
    {
        return $this->orderBy('comment__in');
    }

    /**
     * Most recent first.
     */
    public function latest(): self
    {
        return $this->orderByDate('DESC');
    }

    public function oldest(): self
    {
        return $this->orderByDate('ASC');
    }
}
