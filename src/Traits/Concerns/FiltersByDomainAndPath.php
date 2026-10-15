<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait FiltersByDomainAndPath
{
    protected ?string $domain = null;

    /**
     * @var array<string>|null
     */
    protected ?array $domain_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $domain_NotIn = null;

    protected ?string $path = null;

    /**
     * @var array<string>|null
     */
    protected ?array $path_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $path_NotIn = null;

    public function domain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @param  array<string>  $domains
     */
    public function domainIn(array $domains): self
    {
        $this->domain_In = $domains;

        return $this;
    }

    /**
     * @param  array<string>  $domains
     */
    public function domainNotIn(array $domains): self
    {
        $this->domain_NotIn = $domains;

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param  array<string>  $paths
     */
    public function pathIn(array $paths): self
    {
        $this->path_In = $paths;

        return $this;
    }

    /**
     * @param  array<string>  $paths
     */
    public function pathNotIn(array $paths): self
    {
        $this->path_NotIn = $paths;

        return $this;
    }
}
