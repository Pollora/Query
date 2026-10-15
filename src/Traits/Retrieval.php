<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use WP_Query;

/**
 * Terminal methods that run the query with the arguments that fit their purpose.
 *
 * They work on a copy: the builder keeps its own arguments, so it can still be reused afterwards.
 * Pagination arguments set on the builder are kept unless the method needs to override them,
 * and as with `get()`, WP's own `posts_per_page` default applies when none is set.
 */
trait Retrieval
{
    /**
     * The first matching post (a WP_Post), or null. Returns a post ID with `fields('ids')`,
     * and a partial post object with `fields('id=>parent')`.
     */
    public function first(): object|int|null
    {
        $query = $this->runOnCopy(function (self $copy): void {
            $copy->resetPagination();
            $copy->postsPerPage = 1;
            $copy->noFoundRows = true;
        });

        return $query->posts[0] ?? null;
    }

    /**
     * The IDs of the matching posts (honours `limit()`, `paged()`, `offset()`).
     *
     * @return array<int>
     */
    public function ids(): array
    {
        $query = $this->runOnCopy(function (self $copy): void {
            $copy->fields = 'ids';
            $copy->noFoundRows = true;
        });

        return array_map('intval', $query->posts);
    }

    /**
     * The total number of matching posts, whatever the limit or page set on the builder.
     */
    public function count(): int
    {
        $query = $this->runOnCopy(function (self $copy): void {
            $copy->resetPagination();
            $copy->fields = 'ids';
            $copy->postsPerPage = 1;
            $copy->noFoundRows = false;
            $copy->ignoreStickyPosts = true;
        });

        return (int) $query->found_posts;
    }

    public function exists(): bool
    {
        $query = $this->runOnCopy(function (self $copy): void {
            $copy->resetPagination();
            $copy->fields = 'ids';
            $copy->postsPerPage = 1;
            $copy->noFoundRows = true;
            $copy->ignoreStickyPosts = true;
        });

        return $query->posts !== [];
    }

    private function runOnCopy(callable $configure): WP_Query
    {
        $copy = clone $this;
        $configure($copy);

        return new WP_Query($copy->getArguments());
    }

    private function resetPagination(): void
    {
        $this->nopaging = null;
        $this->offset = null;
        $this->paged = null;
        $this->page = null;
    }
}
