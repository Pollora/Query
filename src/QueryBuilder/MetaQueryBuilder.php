<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

class MetaQueryBuilder extends SubQuery
{
    private ComparisonBuilder $comparisonBuilder;

    protected ?string $state = null;

    private ?string $compareKey = null;

    private ?string $typeKey = null;

    public function __construct(
        private readonly mixed $key,
        private readonly mixed $value = null,
    ) {
        $this->comparisonBuilder = new ComparisonBuilder(null, $this->value);
    }

    public function ofType(string $type): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withType($type);

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

    public function regexp(string $pattern): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::REGEXP, $pattern);

        return $this;
    }

    public function notRegexp(string $pattern): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::NOT_REGEXP, $pattern);

        return $this;
    }

    public function rlike(string $pattern): self
    {
        $this->comparisonBuilder = $this->comparisonBuilder->withComparison(self::RLIKE, $pattern);

        return $this;
    }

    /**
     * Compares the meta key itself (e.g. `LIKE` for keys starting with a prefix) instead of matching it exactly.
     */
    public function keyCompare(string $compare): self
    {
        $this->compareKey = $compare;

        return $this;
    }

    /**
     * Type the meta key is cast to when `keyCompare()` is used.
     */
    public function keyType(string $type): self
    {
        $this->typeKey = strtoupper($type);

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
        return array_filter([
            'key' => $this->key,
            'compare_key' => $this->compareKey,
            'type_key' => $this->typeKey,
            'state' => $this->state,
        ], fn (mixed $value): bool => $value !== null) + $this->comparisonBuilder->buildConfig();
    }
}
