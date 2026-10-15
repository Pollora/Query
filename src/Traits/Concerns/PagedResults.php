<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait PagedResults
{
    protected ?int $paged = null;

    /**
     * Page of results, used together with limit().
     */
    public function paged(int $paged): self
    {
        $this->paged = $paged;

        return $this;
    }
}
