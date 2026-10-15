<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\QueryException;

trait Permission
{
    protected ?string $perm = null;

    /**
     * @throws QueryException If the value is not 'readable' or 'editable'.
     */
    public function userPermission(string $value): self
    {
        if (! $this->isValidPermission($value)) {
            throw new QueryException('Invalid permission supplied: '.$value);
        }

        $this->perm = $value;

        return $this;
    }

    protected function isValidPermission(string $value): bool
    {
        return in_array($value, ['readable', 'editable'], true);
    }
}
