<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

trait Limit
{
    protected ?int $number = null;

    protected ?int $offset = null;

    protected ?int $paged = null;

    /**
     * Maximum number of users. WP returns every user when it is not set.
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

    /**
     * Page of results, used together with limit().
     */
    public function paged(int $paged): self
    {
        $this->paged = $paged;

        return $this;
    }
}
