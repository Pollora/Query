<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Search
{
    protected ?string $s = null;

    protected ?bool $exact = null;

    protected ?bool $sentence = null;

    /**
     * @var array<string>|null
     */
    protected ?array $searchColumns = null;

    public function search(string $keyword): self
    {
        $this->s = $keyword;

        return $this;
    }

    /**
     * Matches the whole keyword instead of a partial match.
     */
    public function exactSearch(bool $exact = true): self
    {
        $this->exact = $exact;

        return $this;
    }

    /**
     * Searches the keyword as a phrase instead of splitting it into terms.
     */
    public function searchSentence(bool $sentence = true): self
    {
        $this->sentence = $sentence;

        return $this;
    }

    /**
     * @param  array<string>  $columns  Among post_title, post_excerpt, post_content.
     */
    public function searchColumns(array $columns): self
    {
        $this->searchColumns = $columns;

        return $this;
    }
}
