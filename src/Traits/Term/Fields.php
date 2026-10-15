<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Term;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Fields
{
    use ValidatesChoices;

    protected string $fields = 'all';

    /**
     * @throws QueryException If the value is not supported by WP_Term_Query.
     */
    public function fields(string $value): self
    {
        $this->fields = $value === '*'
            ? 'all'
            : $this->assertOneOf($value, [
                'all', 'all_with_object_id', 'ids', 'tt_ids', 'names', 'slugs', 'count',
                'id=>parent', 'id=>name', 'id=>slug',
            ], 'fields value');

        return $this;
    }
}
