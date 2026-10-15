# PostQuery

`PostQuery` builds the arguments of [`WP_Query`](https://developer.wordpress.org/reference/classes/wp_query/): posts, pages, attachments and custom post types.

Read [Concepts](concepts.md) first for how builders, defaults and errors work, and [Getting results](results.md) for `first()`, `ids()`, `count()` and `exists()`. Examples assume:

```php
use Pollora\Query\PostQuery;
```

- [Defaults](#defaults)
- [Selecting posts](#selecting-posts)
- [Post type, status and MIME type](#post-type-status-and-mime-type)
- [Authors](#authors)
- [Categories](#categories)
- [Tags](#tags)
- [Taxonomies](#taxonomies)
- [Search](#search)
- [Password](#password)
- [Comment count](#comment-count)
- [Permission](#permission)
- [Dates](#dates)
- [Custom fields](#custom-fields)
- [Limits and pagination](#limits-and-pagination)
- [Order](#order)
- [Fields](#fields)
- [Cache and performance](#cache-and-performance)

## Defaults

```php
PostQuery::select()->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'any',
]
```

- `post_type` is `'any'` (WordPress uses `'post'`), so pages and custom post types are included. Call `postType()` to restrict it. `'any'` still leaves out the post types registered with `exclude_from_search`.
- `fields` is `'all'`.
- Everything else is WordPress' default: for instance only `publish` posts (and the private ones the current user can read) are returned, and the number of posts per page is the *Posts per page* setting.

## Selecting posts

| Method | WordPress argument | Notes |
|---|---|---|
| `PostQuery::find(5)` | `p` | One post by ID. |
| `PostQuery::find([1, 2, 3])` | `post__in` | Several posts by ID. |
| `postId(int)` | `p` | |
| `postSlug(string)` | `name` | The slug of a post, or of a page. |
| `pageId(int)` | `page_id` | |
| `pagename(string)` | `pagename` | The slug of a page. Hierarchical paths work: `'parent/child'`. |
| `title(string)` | `title` | Exact post title. |
| `menuOrder(int)` | `menu_order` | |
| `postParent(int)` | `post_parent` | Children of a post (`0`: top-level only). |
| `whereParentIn(array)` / `whereParentNotIn(array)` | `post_parent__in` / `post_parent__not_in` | |
| `whereIn(array)` / `whereNotIn(array)` | `post__in` / `post__not_in` | Include / exclude by ID. |
| `slugIn(array)` | `post_name__in` | Several slugs. |

```php
PostQuery::select()->postType('page')->pagename('about/team')->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'pagename' => 'about/team',
    'post_type' => 'page',
]
```

An empty list is **not** "no result": `whereIn([])` sends an empty `post__in`, which WordPress ignores, so every post matches. Guard an empty list yourself before building the query.

## Post type, status and MIME type

| Method | WordPress argument |
|---|---|
| `postType(string\|array)` | `post_type` |
| `postStatus(string\|array)` | `post_status` |
| `postMimeType(string\|array)` | `post_mime_type` |

```php
PostQuery::select()
    ->postType(['post', 'event'])
    ->postStatus(['publish', 'future'])
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => [
        'post',
        'event',
    ],
    'post_status' => [
        'publish',
        'future',
    ],
]
```

```php
PostQuery::select()->postType('attachment')->postStatus('inherit')->postMimeType(['image/jpeg', 'image/png'])->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_mime_type' => [
        'image/jpeg',
        'image/png',
    ],
    'post_type' => 'attachment',
    'post_status' => 'inherit',
]
```

## Authors

| Method | WordPress argument |
|---|---|
| `author(int)` | `author` |
| `authorName(string)` | `author_name` (the user's nicename) |
| `authorIn(array)` / `authorNotIn(array)` | `author__in` / `author__not_in` |

```php
PostQuery::select()->postType('post')->authorIn([1, 4])->get();
```

`WP_Query` receives:

```php
[
    'author__in' => [
        1,
        4,
    ],
    'fields' => 'all',
    'post_type' => 'post',
]
```

## Categories

| Method | WordPress argument | Notes |
|---|---|---|
| `cat(int)` | `cat` | |
| `categoryName(string)` | `category_name` | The slug. |
| `categoryAnd(array)` | `category__and` | In **all** these categories. |
| `categoryIn(array)` | `category__in` | In **any** of them. |
| `categoryNotIn(array)` | `category__not_in` | |

```php
PostQuery::select()->postType('post')->categoryAnd([3, 7])->get();
```

`WP_Query` receives:

```php
[
    'category__and' => [
        3,
        7,
    ],
    'fields' => 'all',
    'post_type' => 'post',
]
```

## Tags

| Method | WordPress argument | Notes |
|---|---|---|
| `tag(string)` | `tag` | A slug. |
| `tagId(int)` | `tag_id` | |
| `tagAnd(array)` | `tag__and` | Tag IDs, **all** of them. |
| `tagIn(array)` | `tag__in` | Tag IDs, any of them. |
| `tagNotIn(array)` | `tag__not_in` | |
| `tagSlugAnd(array)` | `tag_slug__and` | Tag slugs, all of them. |
| `tagSlugIn(array)` | `tag_slug__in` | Tag slugs, any of them. |

## Taxonomies

`taxQuery()` filters on any taxonomy, with `where()` / `orWhere()` and nesting. It has [its own page](tax-query.md).

```php
PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query->where($query->taxonomy('product_cat')->searchByTermSlug()->contains(['shoes']));
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'product',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => [
                'shoes',
            ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]
```

## Search

| Method | WordPress argument |
|---|---|
| `search(string)` | `s` |
| `exactSearch(bool = true)` | `exact`: match the whole keyword instead of a partial match |
| `searchSentence(bool = true)` | `sentence`: search the keyword as a phrase instead of splitting it into terms |
| `searchColumns(array)` | `search_columns`: among `post_title`, `post_excerpt`, `post_content` |

```php
PostQuery::select()->search('summer sale')->searchSentence()->searchColumns(['post_title'])->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'any',
    's' => 'summer sale',
    'sentence' => true,
    'search_columns' => [
        'post_title',
    ],
]
```

## Password

| Method | WordPress argument |
|---|---|
| `withPassword()` | `has_password => true`: only password protected posts |
| `withoutPassword()` | `has_password => false`: only posts without a password |
| `withPassword('secret')` | `post_password`: posts protected by that exact password |

## Comment count

`commentCount()` takes a number (exactly that many comments) or WordPress' array form:

```php
PostQuery::select()->commentCount(['value' => 10, 'compare' => '>='])->get();
```

`WP_Query` receives:

```php
[
    'comment_count' => [
        'value' => 10,
        'compare' => '>=',
    ],
    'fields' => 'all',
    'post_type' => 'any',
]
```

## Permission

`userPermission('readable' | 'editable')` (`perm`) keeps the posts the current user can read, or edit. Any other value throws a `QueryException`.

## Dates

Two ways to filter by date:

- **Simple fields**, for one exact value: `year()`, `month()` (`monthnum`), `day()`, `hour()`, `minute()`, `second()`, `week()` (`w`, week of the year, 0 to 53) and `yearMonth()` (`m`, as `YYYYMM`).
- **A date query**, for ranges, several conditions and other columns: `dateQuery()`, documented in [Date queries](date-query.md).

```php
PostQuery::select()->postType('post')->year(2024)->month(3)->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'year' => 2024,
    'monthnum' => 3,
    'post_type' => 'post',
]
```

## Custom fields

Three levels, from simplest to most flexible:

1. **Top-level arguments**: `metaKey()`, `metaValue()`, `metaCompare()`, `metaType()` and, for the key itself, `metaCompareKey()` and `metaTypeKey()` (`meta_key`, `meta_value`, `meta_compare`, `meta_type`, `meta_compare_key`, `meta_type_key`).
2. **`metaQuery()`**, for several conditions and `OR`: [Meta queries](meta-query.md).
3. **`orderByMeta()`**, to sort by a custom field (see [Order](#order)).

```php
PostQuery::select()->postType('product')->metaKey('color')->metaValue('red')->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_key' => 'color',
    'meta_value' => 'red',
    'post_type' => 'product',
]
```

## Limits and pagination

| Method | WordPress argument | Notes |
|---|---|---|
| `limit(int)`, `take(int)`, `postsPerPage(int)` | `posts_per_page` | Three names for the same argument. `-1` is "all". |
| `noPaging(bool = true)` | `nopaging` | Return all posts. |
| `offset(int)`, `skip(int)` | `offset` | When set, WordPress ignores `paged`. Use one or the other. |
| `paged(int)` | `paged` | The page of the archive. |
| `page(int)` | `page` | The page of a paginated *static front page* (`<!--nextpage-->`). Not the same as `paged()`. |
| `postsPerArchivePage(int)` | `posts_per_archive_page` | Only applies to archive pages. |
| `ignoreStickyPosts(bool = true)` | `ignore_sticky_posts` | Stop putting sticky posts first. |

```php
PostQuery::select()->postType('post')->take(10)->paged(3)->ignoreStickyPosts()->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'posts_per_page' => 10,
    'paged' => 3,
    'ignore_sticky_posts' => true,
    'post_type' => 'post',
]
```

## Order

`orderBy($field, $direction = 'DESC')` adds a sort key; call it several times to sort by several fields, in call order. It also takes a map of fields and directions. `orderby` is always sent as an array.

| Method | Meaning |
|---|---|
| `orderBy('title', 'ASC')` | Sort by a field: `post_date`, `title`, `menu_order`, `comment_count`, `post__in`, a named meta clause… |
| `orderBy(['post_date' => 'DESC', 'title' => 'ASC'])` | Several fields at once. |
| `latest()` | `post_date` descending. |
| `oldest()` | `post_date` ascending. |
| `inRandomOrder()` | `rand`. It replaces any other sort key, and a later `orderBy()` replaces it. |
| `orderByMeta($key, $direction = 'DESC', $numeric = false)` | Sort by a custom field (`meta_value`, or `meta_value_num` when `$numeric`). |

```php
PostQuery::select()->postType('event')->orderBy('menu_order', 'ASC')->orderBy('title', 'ASC')->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'orderby' => [
        'menu_order' => 'ASC',
        'title' => 'ASC',
    ],
    'post_type' => 'event',
]
```

`orderByMeta()` also sets `meta_key`, and WordPress then only returns posts that **have** that key.

```php
PostQuery::select()->postType('product')->orderByMeta('price', 'ASC', numeric: true)->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_key' => 'price',
    'orderby' => [
        'meta_value_num' => 'ASC',
    ],
    'post_type' => 'product',
]
```

To sort by a clause of a meta query, name it with `state()` and use that name in `orderBy()`: see [Meta queries](meta-query.md#named-clauses-and-sorting).

An invalid direction throws a `QueryException`.

## Fields

`fields()` (or `PostQuery::select('ids')`) is `'all'`, `'ids'` or `'id=>parent'`. See [Getting results](results.md#fields).

## Cache and performance

| Method | WordPress argument | Notes |
|---|---|---|
| `cacheResults(bool = true)` | `cache_results` | |
| `updateMetaCache(bool = true)` | `update_post_meta_cache` | Pass `false` if you never read the posts' custom fields. |
| `updateTermCache(bool = true)` | `update_post_term_cache` | Pass `false` if you never read the posts' terms. |
| `lazyLoadTermMeta(bool = true)` | `lazy_load_term_meta` | |
| `noFoundRows(bool = true)` | `no_found_rows` | Skips counting the total: faster, but `found_posts` and `max_num_pages` are `0`. |
| `suppressFilters(bool = true)` | `suppress_filters` | |

Turning a cache off is only faster if you do not read what it holds: a `get_post_meta()` in the loop then costs one query per post. For the common cases, the terminal methods already do the right thing: `first()`, `ids()` and `exists()` skip the total count, and `ids()` fetches IDs only.

```php
PostQuery::select()->postType('post')->noFoundRows()->updateMetaCache(false)->updateTermCache(false)->get();
```

`WP_Query` receives:

```php
[
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'no_found_rows' => true,
    'fields' => 'all',
    'post_type' => 'post',
]
```
