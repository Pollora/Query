<?php

declare(strict_types=1);

namespace Pollora\Query\Utils\Types;

final class NumberType implements ValueTypeContract
{
    public const NUMERIC = 'NUMERIC';

    public const DECIMAL = 'DECIMAL';

    public const SIGNED = 'SIGNED';

    public const UNSIGNED = 'UNSIGNED';

    public function detect(mixed $value): ?string
    {
        return match (true) {
            is_int($value) => $value < 0 ? self::SIGNED : self::UNSIGNED,
            is_float($value) => self::DECIMAL,
            is_string($value) && is_numeric($value) => self::NUMERIC,
            default => null,
        };
    }
}
