<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Tag
{
    protected ?string $tag = null;

    protected ?int $tagId = null;

    /**
     * @var array<int>|null
     */
    protected ?array $tag_And = null;

    /**
     * @var array<int>|null
     */
    protected ?array $tag_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $tag_NotIn = null;

    /**
     * @var array<string>|null
     */
    protected ?array $tagSlug_And = null;

    /**
     * @var array<string>|null
     */
    protected ?array $tagSlug_In = null;

    public function tag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function tagId(int $tagId): self
    {
        $this->tagId = $tagId;

        return $this;
    }

    /**
     * @param  array<int>  $tagAnd
     */
    public function tagAnd(array $tagAnd): self
    {
        $this->tag_And = $tagAnd;

        return $this;
    }

    /**
     * @param  array<int>  $tagIn
     */
    public function tagIn(array $tagIn): self
    {
        $this->tag_In = $tagIn;

        return $this;
    }

    /**
     * @param  array<int>  $tagNotIn
     */
    public function tagNotIn(array $tagNotIn): self
    {
        $this->tag_NotIn = $tagNotIn;

        return $this;
    }

    /**
     * @param  array<string>  $tagSlugAnd
     */
    public function tagSlugAnd(array $tagSlugAnd): self
    {
        $this->tagSlug_And = $tagSlugAnd;

        return $this;
    }

    /**
     * @param  array<string>  $tagSlugIn
     */
    public function tagSlugIn(array $tagSlugIn): self
    {
        $this->tagSlug_In = $tagSlugIn;

        return $this;
    }
}
