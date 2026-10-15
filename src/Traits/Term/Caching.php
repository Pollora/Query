<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

use Pollora\Query\Traits\Concerns\CachesResults;

trait Caching
{
    use CachesResults;

    protected ?bool $updateTermMetaCache = null;

    protected ?string $cacheDomain = null;

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
