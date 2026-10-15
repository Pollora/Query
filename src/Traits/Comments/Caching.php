<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

trait Caching
{
    protected ?bool $noFoundRows = null;

    protected ?bool $updateCommentMetaCache = null;

    protected ?bool $updateCommentPostCache = null;

    protected ?string $cacheDomain = null;

    /**
     * Whether the total number of comments is skipped. WP skips it by default, call `noFoundRows(false)`
     * if you need the total or the number of pages.
     */
    public function noFoundRows(bool $value = true): self
    {
        $this->noFoundRows = $value;

        return $this;
    }

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
