<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

use Pollora\Query\PostQuery;

trait Post
{
    protected ?int $p = null;

    protected ?string $name = null;

    protected ?int $postParent = null;

    protected ?int $pageId = null;

    protected ?string $pagename = null;

    protected ?string $title = null;

    protected ?int $menuOrder = null;

    /**
     * @var array<int>|null
     */
    protected ?array $postParent_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $postParent_NotIn = null;

    /**
     * @var array<int>|null
     */
    protected ?array $post_In = null;

    /**
     * @var array<int>|null
     */
    protected ?array $post_NotIn = null;

    /**
     * @var array<string>|null
     */
    protected ?array $postName_In = null;

    public function postId(int $postId): self
    {
        $this->p = $postId;

        return $this;
    }

    public function postSlug(string $postName): self
    {
        $this->name = $postName;

        return $this;
    }

    public function pageId(int $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Page slug, hierarchical paths such as `parent/child` are supported by WP.
     */
    public function pagename(string $pagename): self
    {
        $this->pagename = $pagename;

        return $this;
    }

    /**
     * Exact post title.
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function menuOrder(int $menuOrder): self
    {
        $this->menuOrder = $menuOrder;

        return $this;
    }

    public function postParent(int $postParent): self
    {
        $this->postParent = $postParent;

        return $this;
    }

    /**
     * @param  array<int>  $postParentIn
     * @return Post|PostQuery
     */
    public function whereParentIn(array $postParentIn): self
    {
        $this->postParent_In = $postParentIn;

        return $this;
    }

    /**
     * @param  array<int>  $postParentNotIn
     * @return Post|PostQuery
     */
    public function whereParentNotIn(array $postParentNotIn): self
    {
        $this->postParent_NotIn = $postParentNotIn;

        return $this;
    }

    /**
     * @param  array<int>  $postIn
     * @return Post|PostQuery
     */
    public function whereIn(array $postIn): self
    {
        $this->post_In = $postIn;

        return $this;
    }

    /**
     * @param  array<int>  $postNotIn
     * @return Post|PostQuery
     */
    public function whereNotIn(array $postNotIn): self
    {
        $this->post_NotIn = $postNotIn;

        return $this;
    }

    /**
     * @param  array<string>  $postSlugIn
     * @return Post|PostQuery
     */
    public function slugIn(array $postSlugIn): self
    {
        $this->postName_In = $postSlugIn;

        return $this;
    }
}
