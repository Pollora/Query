<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

trait Caching
{
    protected ?bool $cacheResults = null;

    protected ?bool $updateTermMetaCache = null;

    protected ?string $cacheDomain = null;

    public function cacheResults(bool $value = true): self
    {
        $this->cacheResults = $value;

        return $this;
    }

    public function updateMetaCache(bool $value = true): self
    {
        $this->updateTermMetaCache = $value;

        return $this;
    }

    public function cacheDomain(string $domain): self
    {
        $this->cacheDomain = $domain;

        return $this;
    }
}
