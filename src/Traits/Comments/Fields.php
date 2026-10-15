<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Fields
{
    use ValidatesChoices;

    protected ?string $fields = null;

    /**
     * 'ids' returns comment IDs only, 'all' (or '*') whole comments, which is WP's default.
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
