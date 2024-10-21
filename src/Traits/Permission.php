<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Permission
{
    protected ?string $perm = null;

    public function userPermission(string $value): self
    {
        if ($this->isValidPermission($value)) {
            $this->perm = $value;
        }

        return $this;
    }

    protected function isValidPermission(string $value): bool
    {
        return in_array($value, ['readable', 'editable'], true);
    }
}
