<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Caching
{
    protected ?bool $cacheResults = null;

    protected ?bool $updatePostMetaCache = null;

    protected ?bool $updatePostTermCache = null;

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
}
