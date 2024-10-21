<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

use Pollora\Query\QueryException;

class DateQueryBuilder extends SubQuery
{
    private readonly string $year;

    private readonly string $month;

    private readonly string $day;

    private readonly string $hour;

    private readonly string $minute;

    private readonly string $second;

    /**
     * @var array{
     *     w: int,
     *     d: string
     *  }|string|null
     */
    private array|string|null $after = null;

    /**
     * @var array{
     *     w: int,
     *     d: string
     *  }|string|null
     */
    private array|string|null $before = null;

    final public const POST_CREATED = 'post_date';

    final public const POST_MODIFIED = 'post_modified';

    final public const ALLOWED_KEYS = ['year', 'month', 'day', 'hour', 'minute', 'second'];

    public function __construct(private ?string $column = 'post_date')
    {
    }

    public function created(): self
    {
        $this->column = self::POST_CREATED;

        return $this;
    }

    public function modified(): self
    {
        $this->column = self::POST_MODIFIED;

        return $this;
    }

    /**
     * @param  array<string, string|int|array>  $date
     *
     * @throws QueryException
     */
    private function validateDateArray(array $date): bool
    {
        foreach ($date as $key => $part) {
            if (! in_array($key, self::ALLOWED_KEYS)) {
                throw new QueryException('Invalid key '.$key.' element supplied.');
            }
            $this->$key = $part;
        }

        return true;
    }

    /**
     * @param  array<string, string|int|array>  $date
     */
    private function applyDateArray(array $date): void
    {
        foreach ($date as $key => $part) {
            $this->$key = (int) $part;
        }
    }

    /**
     * @throws QueryException
     */
    public function within(array|string $date, string $extract = 'Ymdhis'): self
    {
        if (is_array($date)) {
            $this->handleArrayDate($date);
        } else {
            $this->handleStringOrNumericDate($date, $extract);
        }

        return $this;
    }

    /**
     * @param  array<string, string|int|array>|string  $toDate
     *
     * @throws QueryException
     */
    public function between(array|string $fromDate, array|string $toDate): self
    {

        $this->after = $this->extractFromDate($fromDate);
        $this->before = $this->extractFromDate($toDate);

        return $this;
    }

    /**
     * @param  array<string, string|int|array>|string  $beforeDate
     *
     * @throws QueryException
     */
    public function before(array|string $beforeDate): self
    {
        $this->before = $this->extractFromDate($beforeDate);

        return $this;
    }

    /**
     * @param  array<string, string|int|array>|string  $afterDate
     *
     * @throws QueryException
     */
    public function after(array|string $afterDate): self
    {
        $this->after = $this->extractFromDate($afterDate);

        return $this;
    }

    /**
     * @param  array<string, string|int|array>|string  $date
     * @return array<string, string|int|array>
     *
     * @throws QueryException
     */
    public function extractFromDate(array|string $date, string $extract = 'Ymdhis'): array
    {
        if (is_array($date)) {
            return $date;
        }

        if (! is_numeric($date)) {
            $date = strtotime($date);
        }

        if ($date === false) {
            throw new QueryException('Provided datestring could not be converted to time');
        }

        return $this->extractDateDetails($date, $extract);
    }

    /**
     * @return array{
     *   year: string,
     *   month: string,
     *   day: string,
     *   jour: string,
     *   minute: string,
     *   second: string,
     * }
     */

    private function extractDateDetails(int $date, string $extract): array
    {
        $extracted = [];
        $mapping = [
            'Y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($mapping as $key => $value) {
            if (str_contains($extract, $key)) {
                $extracted[$value] = date($key, $date);
            }
        }

        var_export($extracted);

        return $extracted;
    }

    /**
     * @param  array<string, string|int|array>  $date
     *
     * @throws QueryException
     */
    private function handleArrayDate(array $date): void
    {
        if ($this->validateDateArray($date)) {
            $this->applyDateArray($date);
        }
    }

    /**
     * @param  array<string, string|int|array>|string  $date
     *
     * @throws QueryException
     */
    private function handleStringOrNumericDate(string|array $date, string $extract): void
    {
        $parts = $this->extractFromDate($date, $extract);
        $this->applyDateArray($parts);
    }

    /**
     * @return array<string, string|int|array>
     */
    public function get(): array
    {
        $beforeIsNotNull = ! is_null($this->before);
        $afterIsNotNull = ! is_null($this->after);

        if ($beforeIsNotNull && $afterIsNotNull) {
            return $this->handleBeforeAndAfter();
        }

        if ($beforeIsNotNull) {
            return $this->handleBeforeOnly();
        }

        if ($afterIsNotNull) {
            return $this->handleAfterOnly();
        }

        return $this->handleDefault();
    }

    /**
     * @return array<string, string|int|array>
     */
    private function handleBeforeAndAfter(): array
    {
        return [
            'column' => $this->column,
            'before' => $this->before,
            'after' => $this->after,
        ];
    }

    /**
     * @return array{
     *    column: string,
     *    before: string|array
     * }
     */
    private function handleBeforeOnly(): array
    {
        return [
            'column' => $this->column,
            'before' => $this->before,
        ];
    }

    /**
     * @return array<string, string|int|array>
     */
    private function handleAfterOnly(): array
    {
        return [
            'column' => $this->column,
            'after' => $this->after,
        ];
    }

    /**
     * @return array{
     *   column: string,
     *   year: string,
     *   month: string,
     *   day: string,
     *   hour: string,
     *   minute: string,
     *   second: string,
     * }
     */
    private function handleDefault(): array
    {
        return [
            'column' => $this->column,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hour' => $this->hour,
            'minute' => $this->minute,
            'second' => $this->second,
        ];
    }
}
