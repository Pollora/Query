<?php

declare(strict_types=1);

namespace Pollora\Query\Utils\Types;

final class BooleanType implements ValueTypeContract
{
    public const BINARY = 'BINARY';

    public function detect(mixed $value): ?string
    {
        return $value === 0 || $value === 1 ? self::BINARY : null;
    }
}
