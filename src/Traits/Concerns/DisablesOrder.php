<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait DisablesOrder
{
    /**
     * Disables the ORDER BY clause.
     */
    public function withoutOrder(): self
    {
        $this->orderby = 'none';

        return $this;
    }
}
