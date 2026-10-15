<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

use Pollora\WordPressArgs\ArgumentHelper;

/**
 * Turns the properties of a query builder into WP query arguments (snake_cased property names).
 * A property named `fooBar_In` becomes `foo_bar__in`, which is how the `__in` style arguments are written.
 */
trait BuildsArguments
{
    use ArgumentHelper;

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->buildArguments();
    }
}
