<?php

declare(strict_types=1);

namespace Pollora\Query\Utils\Types;

final class NumberType implements ValueTypeContract
{
    public const string NUMERIC = 'NUMERIC';

    /**
     * The decimal family. A bare `DECIMAL` is `DECIMAL(10,0)` for MySQL and MariaDB: it rounds to an integer, so the
     * detector never returns it; it returns `DECIMAL(precision,scale)` (see `decimal()`).
     */
    public const string DECIMAL = 'DECIMAL';

    public const string SIGNED = 'SIGNED';

    public const string UNSIGNED = 'UNSIGNED';

    /** The scale a detected decimal has at least: the stored values are rounded to it before they are compared. */
    public const int MIN_SCALE = 10;

    /** The scale a detected decimal has at most (MySQL accepts 30). */
    public const int MAX_SCALE = 30;

    /** The number of digits kept before the decimal point. */
    private const int INTEGER_DIGITS = 20;

    public function detect(mixed $value): ?string
    {
        return match (true) {
            is_int($value) => $value < 0 ? self::SIGNED : self::UNSIGNED,
            is_float($value) => self::decimal(self::scaleOf(rtrim(sprintf('%.15F', $value), '0'))),
            // WordPress casts NUMERIC as SIGNED, an integer: a fraction needs a decimal.
            is_string($value) && is_numeric($value) => self::hasFraction($value)
                ? self::decimal(self::scaleOf(self::plain($value)))
                : self::NUMERIC,
            default => null,
        };
    }

    /**
     * The `DECIMAL(precision,scale)` type that holds a value with the given number of decimals, and the stored values
     * with at least {@see self::MIN_SCALE} decimals.
     */
    public static function decimal(int $scale = 0): string
    {
        $scale = max(self::MIN_SCALE, min($scale, self::MAX_SCALE));

        return sprintf('%s(%d,%d)', self::DECIMAL, $scale + self::INTEGER_DIGITS, $scale);
    }

    /**
     * The scale of a `DECIMAL(precision,scale)` type, or null for any other type.
     */
    public static function scaleOfType(string $type): ?int
    {
        return preg_match('/^DECIMAL\(\d+,(\d+)\)$/', $type, $matches) === 1 ? (int) $matches[1] : null;
    }

    /**
     * Whether a numeric string holds a fraction, which an integer cast would drop.
     */
    private static function hasFraction(string $value): bool
    {
        $number = (float) $value;

        return is_finite($number) && $number !== floor($number);
    }

    /**
     * A numeric string written without exponent.
     */
    private static function plain(string $value): string
    {
        return stripos($value, 'e') === false ? $value : rtrim(sprintf('%.15F', (float) $value), '0');
    }

    /**
     * The number of decimals written after the point of a plain number.
     */
    private static function scaleOf(string $plain): int
    {
        $point = strpos($plain, '.');

        return $point === false ? 0 : strlen(rtrim(substr($plain, $point + 1), '0'));
    }
}
