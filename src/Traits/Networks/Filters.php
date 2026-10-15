<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Networks;

use Pollora\Query\Traits\Concerns\FiltersByDomainAndPath;

trait Filters
{
    use FiltersByDomainAndPath;

    /**
     * @var array<int>|null
     */
    protected ?array $network_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $network_NotIn = null;

    protected ?string $search = null;

    /**
     * @param  array<int>  $networkIds
     */
    public function whereIn(array $networkIds): self
    {
        $this->network_In = $networkIds;

        return $this;
    }

    /**
     * @param  array<int>  $networkIds
     */
    public function whereNotIn(array $networkIds): self
    {
        $this->network_NotIn = $networkIds;

        return $this;
    }

    /**
     * Matches networks whose domain or path contains the keyword. A `*` inside the keyword is a wildcard.
     */
    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }
}
