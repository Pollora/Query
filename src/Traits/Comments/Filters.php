<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Filters
{
    use ValidatesChoices;

    protected ?string $authorEmail = null;

    protected ?string $authorUrl = null;

    /**
     * @var array<int>|null
     */
    protected ?array $author_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $author_NotIn = null;

    /**
     * @var array<int>|null
     */
    protected ?array $comment_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $comment_NotIn = null;

    /**
     * @var array<int|string>|null
     */
    protected ?array $includeUnapproved = null;

    protected ?int $karma = null;

    protected ?int $parent = null;

    /**
     * @var array<int>|null
     */
    protected ?array $parent_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $parent_NotIn = null;

    protected ?int $userId = null;

    protected ?string $search = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $status = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $type = null;

    /**
     * @var array<string>|null
     */
    protected ?array $type_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $type_NotIn = null;

    protected string|bool|null $hierarchical = null;

    public function authorEmail(string $email): self
    {
        $this->authorEmail = $email;

        return $this;
    }

    public function authorUrl(string $url): self
    {
        $this->authorUrl = $url;

        return $this;
    }

    /**
     * Comments written by these registered users.
     *
     * @param  array<int>  $userIds
     */
    public function authorIn(array $userIds): self
    {
        $this->author_In = $userIds;

        return $this;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function authorNotIn(array $userIds): self
    {
        $this->author_NotIn = $userIds;

        return $this;
    }

    /**
     * @param  array<int>  $commentIds
     */
    public function whereIn(array $commentIds): self
    {
        $this->comment_In = $commentIds;

        return $this;
    }

    /**
     * @param  array<int>  $commentIds
     */
    public function whereNotIn(array $commentIds): self
    {
        $this->comment_NotIn = $commentIds;

        return $this;
    }

    /**
     * IDs or emails of users whose unapproved comments are returned whatever the status.
     *
     * @param  array<int|string>  $users
     */
    public function includeUnapproved(array $users): self
    {
        $this->includeUnapproved = $users;

        return $this;
    }

    public function karma(int $karma): self
    {
        $this->karma = $karma;

        return $this;
    }

    /**
     * The direct replies to a comment.
     */
    public function parentId(int $commentId): self
    {
        $this->parent = $commentId;

        return $this;
    }

    /**
     * Top-level comments only.
     */
    public function rootOnly(): self
    {
        return $this->parentId(0);
    }

    /**
     * @param  array<int>  $commentIds
     */
    public function parentIn(array $commentIds): self
    {
        $this->parent_In = $commentIds;

        return $this;
    }

    /**
     * @param  array<int>  $commentIds
     */
    public function parentNotIn(array $commentIds): self
    {
        $this->parent_NotIn = $commentIds;

        return $this;
    }

    public function userId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }

    /**
     * 'hold', 'approve', 'all' (hold and approve only: spam and trash are left out), or a custom status
     * such as 'spam' or 'trash'. WP defaults to 'all'.
     *
     * @param  string|array<string>  $status
     */
    public function status(string|array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function approved(): self
    {
        return $this->status('approve');
    }

    /**
     * Comments awaiting moderation.
     */
    public function pending(): self
    {
        return $this->status('hold');
    }

    /**
     * 'comment', 'pings' (pingbacks and trackbacks) or any custom type.
     *
     * @param  string|array<string>  $type
     */
    public function type(string|array $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param  array<string>  $types
     */
    public function typeIn(array $types): self
    {
        $this->type_In = $types;

        return $this;
    }

    /**
     * @param  array<string>  $types
     */
    public function typeNotIn(array $types): self
    {
        $this->type_NotIn = $types;

        return $this;
    }

    /**
     * Includes the descendants of the matched comments: 'threaded' nests them in a `children` property,
     * 'flat' appends them to the list, false leaves them out. Ignored by WP with `fields('ids')` or count().
     *
     * @throws QueryException
     */
    public function hierarchical(string|bool $mode = 'threaded'): self
    {
        $this->hierarchical = $mode === false ? false : $this->assertOneOf((string) $mode, ['threaded', 'flat'], 'hierarchical mode');

        return $this;
    }

    public function threaded(): self
    {
        return $this->hierarchical('threaded');
    }

    public function flat(): self
    {
        return $this->hierarchical('flat');
    }
}
