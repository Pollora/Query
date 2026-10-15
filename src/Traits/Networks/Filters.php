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
     * Searches the domain and the path. A leading and/or trailing `*` makes it a wildcard search.
     */
    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }
}
