<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Concerns;

/**
 * Terminal methods for the queries whose `query()` returns a list, or a number when `count` is set
 * (WP_Comment_Query, WP_Site_Query, WP_Network_Query).
 *
 * They work on a copy: the builder keeps its own arguments, so it can still be reused afterwards.
 * The using class provides newWpQuery() and, if it has extra arguments to neutralise, prepareCopy().
 */
trait RetrievesRecords
{
    use CopiesBuilder;

    /**
     * Set by count() on a copy only.
     */
    protected ?bool $count = null;

    /**
     * A fresh WP_*_Query object, whose `query(array $args)` method is called with the arguments.
     */
    abstract protected function newWpQuery(): object;

    /**
     * Hook to reset the arguments of a copy that would alter a terminal result.
     */
    protected function prepareCopy(self $copy): void {}

    /**
     * The first matching record, or null. An ID with `fields('ids')`.
     */
    public function first(): object|int|null
    {
        $records = $this->runOnCopy(function (self $copy): void {
            $copy->number = 1;
            $copy->resetPaging();
        });

        return $records[0] ?? null;
    }

    /**
     * @return array<int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
        }));
    }

    /**
     * The total number of matching records, whatever the limit or offset set on the builder.
     */
    public function count(): int
    {
        $args = $this->copyWith(function (self $copy): void {
            $copy->count = true;
            $copy->number = null;
            $copy->resetPaging();
        });

        return (int) $this->newWpQuery()->query($args);
    }

    public function exists(): bool
    {
        return $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
            $copy->number = 1;
            $copy->resetPaging();
        }) !== [];
    }

    /**
     * @return array<mixed>
     */
    private function runOnCopy(callable $configure): array
    {
        $records = $this->newWpQuery()->query($this->copyWith(function (self $copy) use ($configure): void {
            $configure($copy);
            $this->prepareCopy($copy);
        }));

        return is_array($records) ? array_values($records) : [];
    }

    private function resetPaging(): void
    {
        $this->offset = null;

        if (property_exists($this, 'paged')) {
            $this->paged = null;
        }
    }
}
