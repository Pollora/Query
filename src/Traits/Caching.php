<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Caching
{
    protected ?bool $cacheResults = null;

    protected ?bool $updatePostMetaCache = null;

    protected ?bool $updatePostTermCache = null;

    protected ?bool $lazyLoadTermMeta = null;

    protected ?bool $noFoundRows = null;

    protected ?bool $suppressFilters = null;

    public function cacheResults(bool $value = true): self
    {
        $this->cacheResults = $value;

        return $this;
    }

    public function updateMetaCache(bool $value = true): self
    {
        $this->updatePostMetaCache = $value;

        return $this;
    }

    public function updateTermCache(bool $value = true): self
    {
        $this->updatePostTermCache = $value;

        return $this;
    }

    public function lazyLoadTermMeta(bool $value = true): self
    {
        $this->lazyLoadTermMeta = $value;

        return $this;
    }

    /**
     * Skips counting the total rows, faster when pagination is not needed.
     */
    public function noFoundRows(bool $value = true): self
    {
        $this->noFoundRows = $value;

        return $this;
    }

    public function suppressFilters(bool $value = true): self
    {
        $this->suppressFilters = $value;

        return $this;
    }
}
