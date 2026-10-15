<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait CachesResults
{
    protected ?bool $cacheResults = null;

    public function cacheResults(bool $value = true): self
    {
        $this->cacheResults = $value;

        return $this;
    }
}
