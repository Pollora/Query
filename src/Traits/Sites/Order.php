<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Sites;

use Pollora\Query\Traits\Concerns\DisablesOrder;
use Pollora\Query\Traits\Concerns\OrdersByMap;

trait Order
{
    use DisablesOrder, OrdersByMap;

    public function orderByDomain(string $order = 'ASC'): self
    {
        return $this->orderBy('domain', $order);
    }

    public function orderByPath(string $order = 'ASC'): self
    {
        return $this->orderBy('path', $order);
    }

    public function orderByDomainLength(string $order = 'ASC'): self
    {
        return $this->orderBy('domain_length', $order);
    }

    public function orderByPathLength(string $order = 'ASC'): self
    {
        return $this->orderBy('path_length', $order);
    }

    public function orderByLastUpdated(string $order = 'DESC'): self
    {
        return $this->orderBy('last_updated', $order);
    }

    /**
     * Keeps the order of the IDs given to whereIn().
     */
    public function orderByInclude(): self
    {
        return $this->orderBy('site__in');
    }

    /**
     * Keeps the order of the IDs given to networkIn().
     */
    public function orderByNetworkIn(): self
    {
        return $this->orderBy('network__in');
    }

    /**
     * Most recently registered first.
     */
    public function latest(): self
    {
        return $this->orderBy('registered', 'DESC');
    }

    public function oldest(): self
    {
        return $this->orderBy('registered', 'ASC');
    }
}
