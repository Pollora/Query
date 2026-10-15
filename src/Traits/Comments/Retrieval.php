<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\Traits\Concerns\CopiesBuilder;
use WP_Comment_Query;

/**
 * Terminal methods that run the query with the arguments that fit their purpose.
 *
 * They work on a copy: the builder keeps its own arguments, so it can still be reused afterwards.
 */
trait Retrieval
{
    use CopiesBuilder;

    /**
     * Set by count() on a copy only.
     */
    protected ?bool $count = null;

    /**
     * The first matching comment (a WP_Comment), or null. A comment ID with `fields('ids')`.
     */
    public function first(): object|int|null
    {
        $comments = $this->runOnCopy(function (self $copy): void {
            $copy->number = 1;
            $copy->offset = null;
            $copy->paged = null;
            $copy->hierarchical = null;
        });

        return $comments[0] ?? null;
    }

    /**
     * @return array<int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
            $copy->hierarchical = null;
        }));
    }

    /**
     * The total number of matching comments, whatever the limit, offset or page set on the builder.
     */
    public function count(): int
    {
        $copyArgs = $this->copyWith(function (self $copy): void {
            $copy->count = true;
            $copy->number = null;
            $copy->offset = null;
            $copy->paged = null;
            $copy->hierarchical = null;
        });

        return (int) (new WP_Comment_Query)->query($copyArgs);
    }

    public function exists(): bool
    {
        return $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
            $copy->number = 1;
            $copy->offset = null;
            $copy->paged = null;
            $copy->hierarchical = null;
        }) !== [];
    }

    /**
     * @return array<mixed>
     */
    private function runOnCopy(callable $configure): array
    {
        $comments = (new WP_Comment_Query)->query($this->copyWith($configure));

        return is_array($comments) ? array_values($comments) : [];
    }
}
