<?php

declare(strict_types=1);

namespace Pollora\Query;

use Pollora\Query\Traits\Author;
use Pollora\Query\Traits\Caching;
use Pollora\Query\Traits\Category;
use Pollora\Query\Traits\Comment;
use Pollora\Query\Traits\Concerns\BuildsArguments;
use Pollora\Query\Traits\DateQuery;
use Pollora\Query\Traits\Field;
use Pollora\Query\Traits\MetaField;
use Pollora\Query\Traits\MetaQuery;
use Pollora\Query\Traits\MimeType;
use Pollora\Query\Traits\Order;
use Pollora\Query\Traits\Pagination;
use Pollora\Query\Traits\Password;
use Pollora\Query\Traits\Period;
use Pollora\Query\Traits\Permission;
use Pollora\Query\Traits\Post as PostTrait;
use Pollora\Query\Traits\PostType;
use Pollora\Query\Traits\Retrieval;
use Pollora\Query\Traits\Search;
use Pollora\Query\Traits\Status;
use Pollora\Query\Traits\Tag;
use Pollora\Query\Traits\TaxQuery;
use WP_Query;

class PostQuery
{
    use Author, BuildsArguments, Caching, Category, Comment, DateQuery, Field, MetaField, MetaQuery, MimeType, Order, Pagination, Password, Period, Permission, PostTrait, PostType, Retrieval, Search, Status, Tag, TaxQuery;

    public function __construct(array|int|null $postId = null, ?string $fields = null)
    {
        if (is_int($postId)) {
            $this->postId($postId);
        } elseif (is_array($postId)) {
            $this->whereIn($postId);
        }

        if ($fields) {
            $this->fields($fields);
        }
    }

    public static function find(array|int|null $postId = null): self
    {
        return new static($postId);
    }

    public static function select(?string $fields = 'all'): self
    {
        return new static(null, $fields);
    }

    public function get(): WP_Query
    {
        $args = $this->getArguments();

        return new WP_Query($args);
    }
}
