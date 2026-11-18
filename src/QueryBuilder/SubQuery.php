<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

abstract class SubQuery
{
    public const EQUAL = '=';

    public const NOT_EQUAL = '!=';

    public const GREATER = '>';

    public const GREATER_EQUAL = '>=';

    public const LESS = '<';

    public const LESS_EQUAL = '<=';

    public const LIKE = 'LIKE';

    public const NOT_LIKE = 'NOT LIKE';

    public const IN = 'IN';

    public const NOT_IN = 'NOT IN';

    public const BETWEEN = 'BETWEEN';

    public const NOT_BETWEEN = 'NOT BETWEEN';

    public const EXISTS = 'EXISTS';

    public const NOT_EXISTS = 'NOT EXISTS';

    public const REGEXP = 'REGEXP';

    public const NOT_REGEXP = 'NOT REGEXP';

    public const RLIKE = 'RLIKE';

    protected string $compare = 'LIKE';

    /**
     * The WP_Query clause built by this sub-query; may carry a `state` key naming the slot it takes.
     *
     * @return array<string, mixed>
     */
    abstract public function get(): array;
}
