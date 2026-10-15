<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

use Pollora\Query\QueryException;

trait Fields
{
    protected string $fields = 'all';

    /**
     * @throws QueryException If the value is not supported by WP_Term_Query.
     */
    public function fields(string $value): self
    {
        $allowed = [
            'all', 'all_with_object_id', 'ids', 'tt_ids', 'names', 'slugs', 'count',
            'id=>parent', 'id=>name', 'id=>slug',
        ];

        if ($value === '*') {
            $value = 'all';
        }

        if (! in_array($value, $allowed, true)) {
            throw new QueryException('Invalid fields value supplied: '.$value);
        }

        $this->fields = $value;

        return $this;
    }
}
