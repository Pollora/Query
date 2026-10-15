<?php

declare(strict_types=1);

namespace Pollora\Query\Utils;

use Pollora\Query\Utils\Types\BooleanType;
use Pollora\Query\Utils\Types\CharType;
use Pollora\Query\Utils\Types\DatetimeType;
use Pollora\Query\Utils\Types\NullableType;
use Pollora\Query\Utils\Types\NumberType;
use Pollora\Query\Utils\Types\ValueTypeContract;

class ValueTypeDetector
{
    /**
     * @var array<ValueTypeContract>
     */
    private array $detectors = [];

    public function __construct(protected mixed $value, protected ?string $type = null)
    {
        $detectorClasses = [
            BooleanType::class,
            DatetimeType::class,
            NullableType::class,
            NumberType::class,
        ];

        foreach ($detectorClasses as $detectorClass) {
            $this->detectors[] = new $detectorClass;
        }
    }

    public function detect(): string
    {
        if (! is_null($this->type)) {
            return $this->type;
        }

        if (is_array($this->value)) {
            return $this->detectArrayType($this->value);
        }

        return $this->detectType($this->value);
    }

    /**
     * Detects a single type that fits every value of the array.
     *
     * @param  array<mixed>  $values
     */
    private function detectArrayType(array $values): string
    {
        $types = array_values(array_unique(array_map(fn (mixed $value): string => $this->detectType($value), $values)));

        if ($types === []) {
            return CharType::CHAR;
        }

        if (count($types) === 1) {
            return $types[0];
        }

        $numeric = [
            BooleanType::BINARY,
            NumberType::UNSIGNED,
            NumberType::SIGNED,
            NumberType::NUMERIC,
            NumberType::DECIMAL,
        ];

        if (array_diff($types, $numeric) !== []) {
            return CharType::CHAR;
        }

        foreach ([NumberType::DECIMAL, NumberType::NUMERIC, NumberType::SIGNED] as $wider) {
            if (in_array($wider, $types, true)) {
                return $wider;
            }
        }

        return NumberType::UNSIGNED;
    }

    private function detectType(mixed $value): string
    {
        $type = array_reduce(
            $this->detectors,
            fn (?string $carry, ValueTypeContract $detector): ?string => $carry ?? $detector->detect($value)
        );

        return $type ?? CharType::CHAR;
    }
}
