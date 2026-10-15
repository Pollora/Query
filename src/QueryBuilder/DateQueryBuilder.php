<?php

declare(strict_types=1);

namespace Pollora\Query\QueryBuilder;

use Pollora\Query\QueryException;

class DateQueryBuilder extends SubQuery
{
    private ?int $year = null;

    private ?int $month = null;

    private ?int $day = null;

    private ?int $hour = null;

    private ?int $minute = null;

    private ?int $second = null;

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

    final public const USER_REGISTERED = 'user_registered';

    final public const ALLOWED_KEYS = ['year', 'month', 'day', 'hour', 'minute', 'second'];

    private ?string $column;

    private bool $inclusive = false;

    /**
     * Without a column, WP picks the one of the queried table (post_date, user_registered, comment_date).
     */
    public function __construct(?string $column = null)
    {
        $this->column = $column;
    }

    public function created(): self
    {
        $this->column = self::POST_CREATED;

        return $this;
    }

    /**
     * Includes the 'before' and 'after' boundaries themselves in the results.
     */
    public function inclusive(bool $inclusive = true): self
    {
        $this->inclusive = $inclusive;

        return $this;
    }

    public function registered(): self
    {
        $this->column = self::USER_REGISTERED;

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
    private function applyDateArray(array $date): void
    {
        foreach ($date as $key => $part) {
            if (! in_array($key, self::ALLOWED_KEYS, true)) {
                throw new QueryException('Invalid key '.$key.' element supplied.');
            }

            $this->$key = (int) $part;
        }
    }

    /**
     * @throws QueryException
     */
    public function within(array|string $date, string $extract = 'YmdHis'): self
    {
        if (is_array($date)) {
            $this->applyDateArray($date);

            return $this;
        }

        // A date without a time part matches the whole day, not midnight only.
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $extract = 'Ymd';
        }

        $this->applyDateArray($this->extractFromDate($date, $extract));

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
    public function extractFromDate(array|string $date, string $extract = 'YmdHis'): array
    {
        if (is_array($date)) {
            return $date;
        }

        $date = is_numeric($date) ? (int) $date : strtotime($date);

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
            'H' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($mapping as $key => $value) {
            // 'h' is accepted as an alias: WP expects a 24-hour clock, so it is always formatted as 'H'.
            if (str_contains($extract, $key) || ($key === 'H' && str_contains($extract, 'h'))) {
                $extracted[$value] = (int) date($key, $date);
            }
        }

        return $extracted;
    }

    /**
     * @return array<string, string|int|array>
     */
    public function get(): array
    {
        $clause = $this->buildClause();

        if ($this->column === null) {
            unset($clause['column']);
        }

        return $this->inclusive ? $clause + ['inclusive' => true] : $clause;
    }

    /**
     * @return array<string, string|int|array>
     */
    private function buildClause(): array
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
        return ['column' => $this->column] + array_filter([
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hour' => $this->hour,
            'minute' => $this->minute,
            'second' => $this->second,
        ], fn (?int $part): bool => $part !== null);
    }
}
