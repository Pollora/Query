<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\PostQuery;

trait Category
{
    protected ?int $cat = null;

    protected ?string $categoryName = null;

    /**
     * @var array<int>|null
     */
    protected ?array $categoryAnd = null;

    /**
     * @var array<int>|null
     */
    protected ?array $category_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $category_NotIn = null;

    public function cat(int $cat): self
    {
        $this->cat = $cat;

        return $this;
    }

    public function categoryName(string $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * @param  array<int>  $categoryAnd
     * @return PostQuery|Category
     */
    public function categoryAnd(array $categoryAnd): self
    {
        $this->categoryAnd = $categoryAnd;

        return $this;
    }

    /**
     * @param  array<int>  $categoryIn
     * @return PostQuery|Category
     */
    public function categoryIn(array $categoryIn): self
    {
        $this->category_In = $categoryIn;

        return $this;
    }

    /**
     * @param  array<int>  $categoryNotIn
     * @return PostQuery|Category
     */
    public function categoryNotIn(array $categoryNotIn): self
    {
        $this->category_NotIn = $categoryNotIn;

        return $this;
    }
}
