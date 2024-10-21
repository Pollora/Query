<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

use Pollora\Query\Utils\ValueTypeDetector;

class ComparisonBuilder
{
    private ?string $type = null;

    public function __construct(private readonly ?string $compare, private mixed $value)
    {
        $this->type = (new ValueTypeDetector($value, $this->type))->detect();
    }

    public function withComparison(string $compare, mixed $value): self
    {
        return new self($compare, $value, $this->type);
    }

    /**
     * @return array<string, string|int|array>
     */
    public function buildConfig(): array
    {
        $config = [
            'compare' => $this->compare,
            'type' => strtoupper((string) $this->type),
        ];

        if ($this->value !== null) {
            $config['value'] = $this->value;
        }

        return $config;
    }
}
