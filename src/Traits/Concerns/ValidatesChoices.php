<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

use Pollora\Query\QueryException;

trait ValidatesChoices
{
    /**
     * @param  array<string>  $allowed
     *
     * @throws QueryException If the value is not one of the allowed ones.
     */
    protected function assertOneOf(string $value, array $allowed, string $label): string
    {
        if (! in_array($value, $allowed, true)) {
            throw new QueryException('Invalid '.$label.' supplied: '.$value);
        }

        return $value;
    }

    /**
     * @throws QueryException If the direction is not ASC or DESC.
     */
    protected function assertDirection(string $direction): string
    {
        return $this->assertOneOf(strtoupper($direction), ['ASC', 'DESC'], 'order direction');
    }
}
