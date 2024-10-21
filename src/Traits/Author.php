<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\PostQuery;

trait Author
{
    protected ?int $author = null;

    protected ?string $authorName = null;

    /**
     * @var array<int>|null
     */
    protected ?array $author_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $author_NotIn = null;

    public function author(int $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function authorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * @param  array<int>  $authorIn
     * @return PostQuery|Author
     */
    public function authorIn(array $authorIn): self
    {
        $this->author_In = $authorIn;

        return $this;
    }

    /**
     * @param  array<int>  $authorNotIn
     * @return PostQuery|Author
     */
    public function authorNotIn(array $authorNotIn): self
    {
        $this->author_NotIn = $authorNotIn;

        return $this;
    }
}
