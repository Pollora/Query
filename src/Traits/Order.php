<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Order
{
    /**
     * @var array<string, string>|null
     */
    protected ?array $orderby = null;

    /**
     * @param  array<string, string>|string  $orderby  A field, or a map of field => direction.
     */
    public function orderBy(array|string $orderby, string $order = 'DESC'): self
    {
        $columns = is_array($orderby) ? $orderby : [$orderby => $order];

        // 'rand' cannot be combined with other fields, drop it when real columns are added.
        unset($this->orderby['rand']);

        foreach ($columns as $column => $direction) {
            $this->orderby[$column] = strtoupper($direction);
        }

        return $this;
    }

    public function latest(): self
    {
        return $this->orderBy('post_date', 'DESC');
    }

    public function oldest(): self
    {
        return $this->orderBy('post_date', 'ASC');
    }

    public function inRandomOrder(): self
    {
        $this->orderby = ['rand' => 'ASC'];

        return $this;
    }
}
