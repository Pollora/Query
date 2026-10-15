<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\Traits\Concerns\SkipsFoundRows;

trait Caching
{
    use SkipsFoundRows;

    protected ?bool $updateCommentMetaCache = null;

    protected ?bool $updateCommentPostCache = null;

    protected ?string $cacheDomain = null;

    public function updateMetaCache(bool $value = true): self
    {
        $this->updateCommentMetaCache = $value;

        return $this;
    }

    /**
     * Primes the cache of the posts the comments belong to.
     */
    public function updatePostCache(bool $value = true): self
    {
        $this->updateCommentPostCache = $value;

        return $this;
    }

    public function cacheDomain(string $domain): self
    {
        $this->cacheDomain = $domain;

        return $this;
    }
}
