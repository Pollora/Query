<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

use Pollora\Query\QueryException;

trait Order
{
    /**
     * @var array<string, string>|null
     */
    protected ?array $orderby = null;

    /**
     * Fields to sort by, in call order: a user field (ID, display_name, user_login, user_nicename,
     * user_email, user_url, user_registered, post_count...), 'include', 'login__in', 'nicename__in',
     * 'meta_value', 'meta_value_num', a meta key or a meta query clause name.
     *
     * @param  array<string, string>|string  $field  A field, or a map of field => direction.
     *
     * @throws QueryException If a direction is not ASC or DESC.
     */
    public function orderBy(array|string $field, string $order = 'ASC'): self
    {
        $columns = is_array($field) ? $field : [$field => $order];

        foreach ($columns as $column => $direction) {
            $direction = strtoupper($direction);

            if (! in_array($direction, ['ASC', 'DESC'], true)) {
                throw new QueryException('Invalid order direction supplied: '.$direction);
            }

            $this->orderby[$column] = $direction;
        }

        return $this;
    }

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
