<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Sites;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\FiltersByDomainAndPath;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Filters
{
    use FiltersByDomainAndPath, ValidatesChoices;

    /**
     * @var array<int>|null
     */
    protected ?array $site_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $site_NotIn = null;

    protected ?int $networkId = null;

    /**
     * @var array<int>|null
     */
    protected ?array $network_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $network_NotIn = null;

    protected ?int $public = null;

    protected ?int $archived = null;

    protected ?int $mature = null;

    protected ?int $spam = null;

    protected ?int $deleted = null;

    protected ?int $langId = null;

    /**
     * @var array<string>|null
     */
    protected ?array $lang_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $lang_NotIn = null;

    protected ?string $search = null;

    /**
     * @var array<string>|null
     */
    protected ?array $searchColumns = null;

    /**
     * @param  array<int>  $siteIds
     */
    public function whereIn(array $siteIds): self
    {
        $this->site_In = $siteIds;

        return $this;
    }

    /**
     * @param  array<int>  $siteIds
     */
    public function whereNotIn(array $siteIds): self
    {
        $this->site_NotIn = $siteIds;

        return $this;
    }

    /**
     * Sites of one network. WP queries every network when it is not set (or 0).
     */
    public function networkId(int $networkId): self
    {
        $this->networkId = $networkId;

        return $this;
    }

    /**
     * @param  array<int>  $networkIds
     */
    public function networkIn(array $networkIds): self
    {
        $this->network_In = $networkIds;

        return $this;
    }

    /**
     * @param  array<int>  $networkIds
     */
    public function networkNotIn(array $networkIds): self
    {
        $this->network_NotIn = $networkIds;

        return $this;
    }

    /**
     * Public sites, or with false the sites that are not. Without it, WP applies no filter.
     */
    public function wherePublic(bool $value = true): self
    {
        $this->public = (int) $value;

        return $this;
    }

    public function whereArchived(bool $value = true): self
    {
        $this->archived = (int) $value;

        return $this;
    }

    public function whereMature(bool $value = true): self
    {
        $this->mature = (int) $value;

        return $this;
    }

    public function whereSpam(bool $value = true): self
    {
        $this->spam = (int) $value;

        return $this;
    }

    public function whereDeleted(bool $value = true): self
    {
        $this->deleted = (int) $value;

        return $this;
    }

    public function langId(int $langId): self
    {
        $this->langId = $langId;

        return $this;
    }

    /**
     * @param  array<string>  $langIds
     */
    public function langIn(array $langIds): self
    {
        $this->lang_In = $langIds;

        return $this;
    }

    /**
     * @param  array<string>  $langIds
     */
    public function langNotIn(array $langIds): self
    {
        $this->lang_NotIn = $langIds;

        return $this;
    }

    /**
     * Matches sites whose domain or path contains the keyword. A `*` inside the keyword is a wildcard.
     */
    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }

    /**
     * @param  array<string>  $columns  'domain' and/or 'path'.
     *
     * @throws QueryException
     */
    public function searchColumns(array $columns): self
    {
        foreach ($columns as $column) {
            $this->assertOneOf($column, ['domain', 'path'], 'search column');
        }

        $this->searchColumns = $columns;

        return $this;
    }
}
