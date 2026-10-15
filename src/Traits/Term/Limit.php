<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

trait Limit
{
    protected ?int $number = null;

    protected ?int $offset = null;

    /**
     * Maximum number of terms. WP returns every term when it is not set (or 0).
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
