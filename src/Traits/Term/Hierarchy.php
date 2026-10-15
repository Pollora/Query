<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

trait Hierarchy
{
    protected ?bool $hierarchical = null;

    protected ?bool $padCounts = null;

    protected ?string $get = null;

    protected ?int $childOf = null;

    protected ?int $parent = null;

    protected ?bool $childless = null;

    /**
     * Whether terms whose descendants are not empty are kept when hiding empty terms.
     */
    public function hierarchical(bool $hierarchical = true): self
    {
        $this->hierarchical = $hierarchical;

        return $this;
    }

    /**
     * Adds the count of the children to the count of each term.
     */
    public function padCounts(bool $padCounts = true): self
    {
        $this->padCounts = $padCounts;

        return $this;
    }

    /**
     * Returns terms regardless of their ancestry or of whether they are empty (`get => all`).
     * WP then ignores childOf(), childless(), hideEmpty(), hierarchical() and padCounts().
     */
    public function unrestricted(): self
    {
        $this->get = 'all';

        return $this;
    }

    /**
     * All the descendants of the term. Ignored by WP when several taxonomies are queried.
     */
    public function childOf(int $termId): self
    {
        $this->childOf = $termId;

        return $this;
    }

    /**
     * The direct children of the term. For a non-zero ID, WP then ignores childOf().
     */
    public function parentId(int $termId): self
    {
        $this->parent = $termId;

        return $this;
    }

    /**
     * Top-level terms only.
     */
    public function rootOnly(): self
    {
        return $this->parentId(0);
    }

    /**
     * Terms that have no children.
     */
    public function childless(bool $childless = true): self
    {
        $this->childless = $childless;

        return $this;
    }
}
