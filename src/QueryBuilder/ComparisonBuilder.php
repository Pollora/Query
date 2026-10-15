<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

use Pollora\Query\Utils\ValueTypeDetector;

class ComparisonBuilder
{
    private readonly string $type;

    public function __construct(
        private readonly ?string $compare,
        private readonly mixed $value,
        private readonly ?string $forcedType = null,
    ) {
        $this->type = (new ValueTypeDetector($value, $forcedType))->detect();
    }

    public function withComparison(string $compare, mixed $value): self
    {
        return new self($compare, $value, $this->forcedType);
    }

    public function withType(?string $type): self
    {
        return new self($this->compare, $this->value, $type);
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function buildConfig(): array
    {
        $config = [
            'compare' => $this->compare,
            'type' => strtoupper($this->type),
        ];

        if ($this->value !== null) {
            $config['value'] = $this->value;
        }

        return $config;
    }
}
