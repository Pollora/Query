# CommentQuery

`CommentQuery` builds the arguments of [`WP_Comment_Query`](https://developer.wordpress.org/reference/classes/wp_comment_query/).

Read [Concepts](concepts.md) first, and [Getting results](results.md) for the terminal methods. Examples assume:

```php
use Pollora\Query\CommentQuery;
```

- [Defaults](#defaults)
- [Selecting comments](#selecting-comments)
- [Status and type](#status-and-type)
- [Replies](#replies)
- [The post a comment belongs to](#the-post-a-comment-belongs-to)
- [Order](#order)
- [Limit and pagination](#limit-and-pagination)
- [Fields](#fields)
- [Meta and dates](#meta-and-dates)
- [Cache](#cache)
- [Results](#results)

## Defaults

```php
CommentQuery::select()->get();
```

`WP_Comment_Query` receives:

```php
[]
```

The builder sends nothing, so WordPress' own defaults apply, and two of them matter:

- **`status` is `'all'`, which is pending *and* approved comments. Spam and trashed comments are left out.** The builder keeps it that way on purpose: listing spam by default would be unsafe. Ask for them with `status('spam')` or `status('trash')`.
- **`no_found_rows` is `true`**: the total is not counted, so `found_comments` and `max_num_pages` are `0` on the query object. Call `noFoundRows(false)` if you paginate. `count()` is not affected.

## Selecting comments

| Method | WordPress argument | Notes |
|---|---|---|
| `CommentQuery::find(5)`, `find([1, 2])` | `comment__in` | |
| `whereIn(array)` | `comment__in` | |
| `whereNotIn(array)` | `comment__not_in` | |
| `authorEmail(string)` | `author_email` | |
| `authorUrl(string)` | `author_url` | |
| `authorIn(array)` / `authorNotIn(array)` | `author__in` / `author__not_in` | IDs of the registered users who wrote the comments. |
| `userId(int)` | `user_id` | |
| `karma(int)` | `karma` | |
| `search(string)` | `search` | The keyword in the author, email, URL, IP address or content (contains). |
| `includeUnapproved(array)` | `include_unapproved` | IDs or emails of users whose pending comments are returned **whatever the status**. |

An empty `whereIn([])` is **not** "no result": WordPress ignores an empty `comment__in`, so every comment matches. Guard an empty list yourself.

## Status and type

| Method | WordPress argument | Notes |
|---|---|---|
| `status(string\|array)` | `status` | `'hold'`, `'approve'`, `'all'`, `'spam'`, `'trash'` or a custom status. |
| `approved()` | `status => 'approve'` | |
| `pending()` | `status => 'hold'` | Awaiting moderation. |
| `type(string\|array)` | `type` | `'comment'`, `'pings'` (pingbacks and trackbacks) or any custom type. |
| `typeIn(array)` / `typeNotIn(array)` | `type__in` / `type__not_in` | |

```php
CommentQuery::select()->status(['approve', 'hold'])->typeNotIn(['pingback', 'trackback'])->get();
```

`WP_Comment_Query` receives:

```php
[
    'status' => [
        'approve',
        'hold',
    ],
    'type__not_in' => [
        'pingback',
        'trackback',
    ],
]
```

## Replies

| Method | WordPress argument | Notes |
|---|---|---|
| `rootOnly()` | `parent => 0` | Top-level comments. |
| `parentId(int)` | `parent` | The direct replies to a comment. |
| `parentIn(array)` / `parentNotIn(array)` | `parent__in` / `parent__not_in` | |
| `hierarchical('threaded' \| 'flat' \| false)` | `hierarchical` | Include the descendants of the matches. `'threaded'` nests them in a `children` property, `'flat'` appends them to the list. Any other value throws a `QueryException`. |
| `threaded()` | `hierarchical => 'threaded'` | |
| `flat()` | `hierarchical => 'flat'` | |

WordPress ignores `hierarchical` when only IDs or a count are asked.

```php
CommentQuery::select()->postId(42)->approved()->rootOnly()->threaded()->get();
```

`WP_Comment_Query` receives:

```php
[
    'parent' => 0,
    'status' => 'approve',
    'hierarchical' => 'threaded',
    'post_id' => 42,
]
```

## The post a comment belongs to

| Method | WordPress argument |
|---|---|
| `postId(int)` | `post_id` |
| `postIn(array)` / `postNotIn(array)` | `post__in` / `post__not_in` |
| `postAuthor(int)` | `post_author` |
| `postAuthorIn(array)` / `postAuthorNotIn(array)` | `post_author__in` / `post_author__not_in` |
| `postName(string)` | `post_name` |
| `postParent(int)` | `post_parent` |
| `postStatus(string\|array)` | `post_status` (`'any'` is accepted) |
| `postType(string\|array)` | `post_type` (`'any'` is accepted) |

```php
CommentQuery::select()->postType('product')->postStatus('publish')->type('review')->get();
```

`WP_Comment_Query` receives:

```php
[
    'type' => 'review',
    'post_status' => 'publish',
    'post_type' => 'product',
]
```

## Order

`orderBy($field, $direction = 'ASC')` adds a sort key, in call order; it also takes a map of fields and directions. Each field has its own direction. WordPress' default (no `orderBy()`) is the newest first (`comment_date_gmt DESC`).

| Method | Sorts by |
|---|---|
| `orderBy($field, $direction)` | `comment_date`, `comment_date_gmt`, `comment_author`, `comment_author_email`, `comment_content`, `comment_karma`, `comment_parent`, `comment_post_ID`, `comment_type`, `user_id`, `comment_ID`, `meta_value`, `meta_value_num`, a meta key or a meta clause name… |
| `orderByDate($direction = 'DESC')` | `comment_date_gmt` |
| `orderByAuthor($direction = 'ASC')` | `comment_author` |
| `orderByInclude()` | `comment__in`: keep the order of `whereIn()` |
| `latest()` | `comment_date_gmt` descending |
| `oldest()` | `comment_date_gmt` ascending |
| `orderByMeta($key, $direction, $numeric)` | `meta_value` or `meta_value_num`, and sets `meta_key` |
| `withoutOrder()` | no `ORDER BY` (`'none'`) |

An invalid direction throws a `QueryException`. After `withoutOrder()`, any `orderBy()` starts a new sort.

```php
CommentQuery::select()->postId(42)->oldest()->get();
```

`WP_Comment_Query` receives:

```php
[
    'orderby' => [
        'comment_date_gmt' => 'ASC',
    ],
    'post_id' => 42,
]
```

## Limit and pagination

| Method | WordPress argument |
|---|---|
| `limit(int)`, `take(int)` | `number` |
| `offset(int)`, `skip(int)` | `offset` (applied only with a limit) |
| `paged(int)` | `paged` |

## Fields

`fields('ids')` (or `CommentQuery::select('ids')`) returns comment IDs; `'all'` or `'*'` returns whole `WP_Comment` objects, which is the default. Anything else throws a `QueryException`.

## Meta and dates

`metaKey()`, `metaValue()`, `metaQuery()`… work as for posts: see [Meta queries](meta-query.md). `dateQuery()` filters on the comment date (`comment_date` unless you name a column): see [Date queries](date-query.md).

```php
CommentQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('rating')->ofType('NUMERIC')->greaterOrEqualTo(4));
    })
    ->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-01-01'));
    })
    ->get();
```

`WP_Comment_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'AND',
        [
            'after' => [
                'year' => 2024,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
        ],
    ],
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'rating',
            'compare' => '>=',
            'type' => 'NUMERIC',
            'value' => 4,
        ],
    ],
]
```

## Cache

| Method | WordPress argument | Notes |
|---|---|---|
| `noFoundRows(bool = true)` | `no_found_rows` | `noFoundRows(false)` counts the total (see [Defaults](#defaults)). |
| `updateMetaCache(bool = true)` | `update_comment_meta_cache` | |
| `updatePostCache(bool = true)` | `update_comment_post_cache` | Primes the cache of the posts the comments belong to. |
| `cacheDomain(string)` | `cache_domain` | |

## Results

`get()` returns the `WP_Comment_Query` (the comments are in `->comments`). The [terminal methods](results.md) are:

```php
$comment = CommentQuery::select()->postId(42)->latest()->first();   // WP_Comment|null
$ids     = CommentQuery::select()->postId(42)->approved()->ids();
$pending = CommentQuery::select()->pending()->count();
$has     = CommentQuery::select()->authorEmail('me@example.com')->exists();
```
