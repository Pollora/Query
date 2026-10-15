<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Field
{
    use ValidatesChoices;

    protected string $fields = 'all';

    /**
     * @throws QueryException If the value is not one of '*', 'all', 'ids' or 'id=>parent'.
     */
    public function fields(string $value): self
    {
        $this->fields = $value === '*'
            ? 'all'
            : $this->assertOneOf($value, ['all', 'ids', 'id=>parent'], 'fields value');

        return $this;
    }
}
