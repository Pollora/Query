<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

trait Filters
{
    /**
     * @var string|array<string>|null
     */
    protected string|array|null $taxonomy = null;

    /**
     * @var int|array<int>|null
     */
    protected int|array|null $objectIds = null;

    /**
     * @var array<int>|null
     */
    protected ?array $include = null;

    /**
     * @var array<int>|null
     */
    protected ?array $exclude = null;

    /**
     * @var array<int>|null
     */
    protected ?array $excludeTree = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $name = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $slug = null;

    /**
     * @var int|array<int>|null
     */
    protected int|array|null $termTaxonomyId = null;

    protected ?string $search = null;

    protected ?string $name_Like = null;

    protected ?string $description_Like = null;

    /**
     * Whether terms without any post are hidden. Unlike WP (which hides them by default),
     * empty terms are returned unless asked otherwise, as no constraint is applied by default.
     */
    protected bool $hideEmpty = false;

    /**
     * @param  string|array<string>  $taxonomy
     */
    public function taxonomy(string|array $taxonomy): self
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * Limits the terms to those attached to the given objects (posts, users...).
     *
     * @param  int|array<int>  $objectIds
     */
    public function objectIds(int|array $objectIds): self
    {
        $this->objectIds = $objectIds;

        return $this;
    }

    /**
     * @param  array<int>  $termIds
     */
    public function whereIn(array $termIds): self
    {
        $this->include = $termIds;

        return $this;
    }

    /**
     * Ignored by WP when whereIn() is used.
     *
     * @param  array<int>  $termIds
     */
    public function whereNotIn(array $termIds): self
    {
        $this->exclude = $termIds;

        return $this;
    }

    /**
     * Excludes the terms and all their descendants.
     *
     * @param  array<int>  $termIds
     */
    public function excludeTree(array $termIds): self
    {
        $this->excludeTree = $termIds;

        return $this;
    }

    /**
     * @param  string|array<string>  $name
     */
    public function name(string|array $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  string|array<string>  $slug
     */
    public function slug(string|array $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param  int|array<int>  $termTaxonomyId
     */
    public function termTaxonomyId(int|array $termTaxonomyId): self
    {
        $this->termTaxonomyId = $termTaxonomyId;

        return $this;
    }

    /**
     * Matches the keyword in the name, the slug and the description.
     */
    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }

    public function nameLike(string $name): self
    {
        $this->name_Like = $name;

        return $this;
    }

    public function descriptionLike(string $description): self
    {
        $this->description_Like = $description;

        return $this;
    }

    public function hideEmpty(bool $hide = true): self
    {
        $this->hideEmpty = $hide;

        return $this;
    }

    public function withEmpty(): self
    {
        return $this->hideEmpty(false);
    }
}
