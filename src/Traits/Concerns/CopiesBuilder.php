<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

/**
 * Lets terminal methods tweak a copy of the builder, so the builder itself is never altered.
 */
trait CopiesBuilder
{
    /**
     * @return array<string, mixed> The arguments of the copy once configured.
     */
    private function copyWith(callable $configure): array
    {
        $copy = clone $this;
        $configure($copy);

        return $copy->getArguments();
    }
}
