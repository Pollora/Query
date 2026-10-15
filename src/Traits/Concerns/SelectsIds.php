<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

use Pollora\Query\QueryException;

trait SelectsIds
{
    use ValidatesChoices;

    protected ?string $fields = null;

    /**
     * 'ids' returns IDs only, 'all' (or '*') whole objects, which is WP's default.
     *
     * @throws QueryException
     */
    public function fields(string $value): self
    {
        $value = $value === '*' ? 'all' : $value;
        $this->assertOneOf($value, ['all', 'ids'], 'fields value');

        $this->fields = $value === 'all' ? null : $value;

        return $this;
    }
}
