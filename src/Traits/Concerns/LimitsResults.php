<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

trait LimitsResults
{
    protected ?int $number = null;

    protected ?int $offset = null;

    /**
     * Maximum number of results.
     */
    public function limit(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function take(int $number): self
    {
        return $this->limit($number);
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }
}
