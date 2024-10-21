<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Field
{
    protected string $fields = 'all';

    public function fields(string $value): self
    {
        if ($value === '*') {
            $this->fields = 'all';
        } elseif (in_array($value, ['all', 'ids', 'id=>parent'])) {
            $this->fields = $value;
        }

        return $this;
    }
}
