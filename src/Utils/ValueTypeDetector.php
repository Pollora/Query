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
            $this->detectors[] = new $detectorClass();
        }
    }

    public function detect(): string
    {
        if (is_array($this->value)) {
            $this->value = $this->value[0];
        }

        if (! is_null($this->type)) {
            return $this->type;
        }

        return $this->detectType();
    }

    private function detectType(): ?string
    {
        $type = array_reduce(
            $this->detectors,
            function ($carry, $detector) {
                return $carry ?? $detector->detect($this->value);
            }
        );

        return $type ?? CharType::CHAR;
    }
}
