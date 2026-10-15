<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

use Pollora\Query\Traits\Concerns\OrdersByMap;

trait Order
{
    use OrdersByMap;

    public function orderByName(string $order = 'ASC'): self
    {
        return $this->orderBy('display_name', $order);
    }

    public function orderByLogin(string $order = 'ASC'): self
    {
        return $this->orderBy('user_login', $order);
    }

    public function orderByEmail(string $order = 'ASC'): self
    {
        return $this->orderBy('user_email', $order);
    }

    public function orderByPostCount(string $order = 'DESC'): self
    {
        return $this->orderBy('post_count', $order);
    }

    /**
     * Keeps the order of the IDs given to whereIn().
     */
    public function orderByInclude(): self
    {
        return $this->orderBy('include');
    }

    /**
     * Keeps the order of the logins given to loginIn().
     */
    public function orderByLoginIn(): self
    {
        return $this->orderBy('login__in');
    }

    /**
     * Keeps the order of the nicenames given to nicenameIn().
     */
    public function orderByNicenameIn(): self
    {
        return $this->orderBy('nicename__in');
    }

    /**
     * Most recently registered first.
     */
    public function latest(): self
    {
        return $this->orderBy('user_registered', 'DESC');
    }

    public function oldest(): self
    {
        return $this->orderBy('user_registered', 'ASC');
    }
}
