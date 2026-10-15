<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

trait Performance
{
    protected ?bool $cacheResults = null;

    protected ?bool $countTotal = null;

    public function cacheResults(bool $value = true): self
    {
        $this->cacheResults = $value;

        return $this;
    }

    /**
     * Whether the total number of users is counted. Disable it when pagination is not needed.
     */
    public function countTotal(bool $value = true): self
    {
        $this->countTotal = $value;

        return $this;
    }

    public function withoutCount(): self
    {
        return $this->countTotal(false);
    }
}
