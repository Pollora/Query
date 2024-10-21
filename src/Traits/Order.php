<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Order
{
    protected array|string|null $orderby = null;

    public function orderBy(array|string $orderby, array|string $order = 'DESC'): self
    {
        if (! $this->orderby) {
            $this->orderby = [];
        }
        $this->orderby[$orderby] = $order;

        return $this;
    }

    public function latest(): self
    {
        $this->orderby['post_date'] = 'DESC';

        return $this;
    }

    public function oldest(): self
    {
        $this->orderby['post_date'] = 'ASC';

        return $this;
    }

    public function inRandomOrder(): self
    {
        $this->orderby = 'rand';

        return $this;
    }
}
