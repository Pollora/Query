<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\Comments;

/**
 * Filters on the post a comment belongs to.
 */
trait PostFilters
{
    protected ?int $postId = null;

    /**
     * @var array<int>|null
     */
    protected ?array $post_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $post_NotIn = null;

    protected ?int $postAuthor = null;

    /**
     * @var array<int>|null
     */
    protected ?array $postAuthor_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $postAuthor_NotIn = null;

    protected ?string $postName = null;

    protected ?int $postParent = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $postStatus = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $postType = null;

    public function postId(int $postId): self
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * @param  array<int>  $postIds
     */
    public function postIn(array $postIds): self
    {
        $this->post_In = $postIds;

        return $this;
    }

    /**
     * @param  array<int>  $postIds
     */
    public function postNotIn(array $postIds): self
    {
        $this->post_NotIn = $postIds;

        return $this;
    }

    public function postAuthor(int $userId): self
    {
        $this->postAuthor = $userId;

        return $this;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function postAuthorIn(array $userIds): self
    {
        $this->postAuthor_In = $userIds;

        return $this;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function postAuthorNotIn(array $userIds): self
    {
        $this->postAuthor_NotIn = $userIds;

        return $this;
    }

    public function postName(string $slug): self
    {
        $this->postName = $slug;

        return $this;
    }

    public function postParent(int $postId): self
    {
        $this->postParent = $postId;

        return $this;
    }

    /**
     * @param  string|array<string>  $status  Post status, or 'any'.
     */
    public function postStatus(string|array $status): self
    {
        $this->postStatus = $status;

        return $this;
    }

    /**
     * @param  string|array<string>  $type  Post type, or 'any'.
     */
    public function postType(string|array $type): self
    {
        $this->postType = $type;

        return $this;
    }
}
