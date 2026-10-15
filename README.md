<p align="center">
  <a href="https://pollora.dev">
    <img src="https://raw.githubusercontent.com/Pollora/.github/main/brand/banners/Query.png" width="100%" alt="Query: fluent builders for the WordPress query classes">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/pollora/query"><img src="https://img.shields.io/packagist/v/pollora/query" alt="Latest version"></a>
  <a href="https://packagist.org/packages/pollora/query"><img src="https://img.shields.io/packagist/dt/pollora/query" alt="Total downloads"></a>
  <a href="https://github.com/Pollora/Query/actions/workflows/ci.yml"><img src="https://github.com/Pollora/Query/actions/workflows/ci.yml/badge.svg" alt="Tests"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/Pollora/Query" alt="License"></a>
</p>

Query builds the arguments of the WordPress query classes (`WP_Query`, `WP_Term_Query`, `WP_User_Query`, `WP_Comment_Query`, `WP_Site_Query` and `WP_Network_Query`) with chained, type-hinted methods instead of nested arrays. Tax, meta and date sub-queries are built with `where()` and `orWhere()`, and the `relation` keys and the nesting are written for you.

It is for WordPress developers who are tired of looking up array keys and debugging misplaced `relation` entries.

> Part of [Pollora](https://pollora.dev), the Laravel framework for WordPress. In a Pollora project `PostQuery` is already installed: use the `Pollora\Support\Facades\PostQuery` facade.

## Installation

```bash
composer require pollora/query
```

Requires PHP 8.2+ and WordPress. If your WordPress site does not use Composer yet, see [this article on managing WordPress with Composer](https://deliciousbrains.com/storing-wordpress-in-git/).

## Quick start

The five latest published articles:

```php
use Pollora\Query\PostQuery;

$query = PostQuery::select()
    ->postType('post')
    ->postStatus('publish')
    ->latest()
    ->take(5)
    ->get(); // WP_Query
```

Active products in a category, with a tax query and a meta query:

```php
use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\TaxQuery;

$query = PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query->taxonomy('category')->contains(['tee-shirt', 'sportswear'])->searchByTermSlug()
        );
    })
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('status')->equalTo('active'));
    })
    ->get();
```

You rarely need the whole `WP_Query` object: terminal methods return what you actually want.

```php
$latest = PostQuery::select()->postType('post')->latest()->first();   // WP_Post or null
$ids    = PostQuery::select()->postType('page')->limit(20)->ids();    // [12, 15, 31]
$total  = PostQuery::select()->postType('post')->postStatus('publish')->count();
$exists = PostQuery::select()->postType('page')->postSlug('about')->exists();
```

The other query classes work the same way:

```php
use Pollora\Query\CommentQuery;
use Pollora\Query\TermQuery;
use Pollora\Query\UserQuery;

$categories = TermQuery::select()->taxonomy('category')->rootOnly()->orderByCount()->get();
$editors    = UserQuery::select()->roleIn(['editor', 'author'])->latest()->limit(20)->get();
$pending    = CommentQuery::select()->postId(42)->pending()->count();
```

To see the arguments without running the query, use `getArguments()`:

```php
PostQuery::select()->postType('page')->postParent(5)->getArguments();
```

`getArguments()` returns:

```php
[
    'fields' => 'all',
    'post_parent' => 5,
    'post_type' => 'page',
]
```

## The query classes

| Class | Builds the arguments of | Documentation |
|---|---|---|
| `PostQuery` | `WP_Query`: posts, pages, custom post types | [PostQuery](docs/post-query.md) |
| `TermQuery` | `WP_Term_Query`: categories, tags, custom taxonomies | [TermQuery](docs/term-query.md) |
| `UserQuery` | `WP_User_Query` | [UserQuery](docs/user-query.md) |
| `CommentQuery` | `WP_Comment_Query` | [CommentQuery](docs/comment-query.md) |
| `SiteQuery`, `NetworkQuery` | `WP_Site_Query`, `WP_Network_Query` (multisite) | [SiteQuery and NetworkQuery](docs/site-network-query.md) |

The sub-queries are shared: [`TaxQuery`](docs/tax-query.md) (posts), [`MetaQuery`](docs/meta-query.md) (posts, terms, users, comments, sites) and [`DateQuery`](docs/date-query.md) (posts, users, comments, sites).

## Documentation

- [Concepts](docs/concepts.md): how the builders work, defaults, errors, `where()` / `orWhere()`, raw arguments. **Start here.**
- [Getting results](docs/results.md): `get()`, `first()`, `ids()`, `count()`, `exists()` and friends.
- [Tax queries](docs/tax-query.md), [meta queries](docs/meta-query.md), [date queries](docs/date-query.md).
- [Upgrading](docs/upgrading.md): what changed since 1.0.

## Why use it

Compared with passing an array to `WP_Query`:

- Chained methods with descriptive names, which read top to bottom.
- Type-hinted parameters and IDE autocompletion, instead of array keys to remember.
- Invalid values (an unknown field, a wrong order direction) throw a `QueryException` instead of being silently ignored.
- Tax, meta and date sub-queries without hand-written `relation` keys.

### A complete example

A query combining a post type, a tax query, a date query, a meta query, a permission and caching (with the `use` statements of the classes above, plus `Pollora\Query\DateQuery`):

```php
PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query->taxonomy('category')->contains(['tee-shirt', 'sportswear'])->searchByTermSlug()
        );
    })
    ->dateQuery(function (DateQuery $query) {
        $query->where(
            $query->date('posted_at')->between('2021-01-01', '2022-02-01')
        )->orWhere(
            $query->date()->created()->after('2021-01-01')
        );
    })
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('status')->equalTo('active'))
            ->orWhere($query->meta('highlighted')->equalTo(0));
    })
    ->userPermission('readable')
    ->cacheResults()
    ->get();
```

`WP_Query` receives:

```php
[
    'cache_results' => true,
    'date_query' => [
        'relation' => 'OR',
        [
            'column' => 'posted_at',
            'before' => [
                'year' => 2022,
                'month' => 2,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
            'after' => [
                'year' => 2021,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
        ],
        [
            'column' => 'post_date',
            'after' => [
                'year' => 2021,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
        ],
    ],
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'status',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'active',
        ],
        [
            'key' => 'highlighted',
            'compare' => '=',
            'type' => 'BINARY',
            'value' => 0,
        ],
    ],
    'perm' => 'readable',
    'post_type' => 'product',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => [
                'tee-shirt',
                'sportswear',
            ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]
```

The same query as a `WP_Query` array is much harder to read and to get right.

## Testing

```bash
vendor/bin/pest
```

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/Pollora/.github/blob/main/CONTRIBUTING.md). Report security issues privately, as described in the [security policy](https://github.com/Pollora/.github/blob/main/SECURITY.md).

## License

Query is open-source software licensed under the [MIT license](LICENSE). © [RuBee group](https://rubee.group)
