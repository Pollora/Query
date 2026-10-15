<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Networks;

use Pollora\Query\Traits\Concerns\SkipsFoundRows;

trait Caching
{
    use SkipsFoundRows;

    protected ?bool $updateNetworkCache = null;

    /**
     * Primes the cache of the found networks.
     */
    public function updateCache(bool $value = true): self
    {
        $this->updateNetworkCache = $value;

        return $this;
    }
}
