<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

class MetaQueryBuilder extends SubQuery
{
    protected ?string $type = null;

    private ComparisonBuilder $comparisonBuilder;

    protected ?string $state = null;

    public function __construct(
        private readonly mixed $key,
        private readonly mixed $value = null,
    ) {
        $this->comparisonBuilder = new ComparisonBuilder($this->value, $this->type ?? null);
    }

    public function ofType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function greaterThan(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::GREATER, $value);

        return $this;
    }

    public function greaterOrEqualTo(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::GREATER_EQUAL, $value);

        return $this;
    }

    public function lessThan(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::LESS, $value);

        return $this;
    }

    public function lessOrEqualTo(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::LESS_EQUAL, $value);

        return $this;
    }

    public function equalTo(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::EQUAL, $value);

        return $this;
    }

    public function notEqualTo(mixed $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_EQUAL, $value);

        return $this;
    }

    public function between(mixed $lowerBoundary, mixed $upperBoundary): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::BETWEEN, [
            $lowerBoundary,
            $upperBoundary,
        ]);

        return $this;
    }

    public function notBetween(mixed $lowerBoundary, mixed $upperBoundary): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_BETWEEN, [
            $lowerBoundary,
            $upperBoundary,
        ]);

        return $this;
    }

    public function like(string $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::LIKE, $value);

        return $this;
    }

    public function notLike(string $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_LIKE, $value);

        return $this;
    }

    /**
     * @param  array<array|int|string>  $value
     * @return $this
     */
    public function in(array $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::IN, $value);

        return $this;
    }

    /**
     * @param  array<array|int|string>  $value
     * @return $this
     */
    public function notIn(array $value): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_IN, $value);

        return $this;
    }

    public function exists(): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::EXISTS, null);

        return $this;
    }

    public function notExists(): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_EXISTS, null);

        return $this;
    }

    public function state(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return array<string, string|int|array>
     */
    public function get(): array
    {
        return ['key' => $this->key, 'state' => $this->state, ...$this->comparisonBuilder->buildConfig()];
    }
}
