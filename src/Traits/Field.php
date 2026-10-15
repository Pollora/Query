<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryException;

trait Field
{
    protected string $fields = 'all';

    /**
     * @throws QueryException If the value is not one of '*', 'all', 'ids' or 'id=>parent'.
     */
    public function fields(string $value): self
    {
        if ($value === '*') {
            $this->fields = 'all';
        } elseif (in_array($value, ['all', 'ids', 'id=>parent'], true)) {
            $this->fields = $value;
        } else {
            throw new QueryException('Invalid fields value supplied: '.$value);
        }

        return $this;
    }
}
