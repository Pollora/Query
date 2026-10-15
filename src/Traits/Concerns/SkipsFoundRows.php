<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait SkipsFoundRows
{
    protected ?bool $noFoundRows = null;

    /**
     * Whether the total number of results is skipped. Some WP queries skip it by default, call
     * `noFoundRows(false)` if you need the total or the number of pages.
     */
    public function noFoundRows(bool $value = true): self
    {
        $this->noFoundRows = $value;

        return $this;
    }
}
