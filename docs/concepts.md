# Concepts

This page explains how the builders work. It applies to every query class: [`PostQuery`](post-query.md), [`TermQuery`](term-query.md), [`UserQuery`](user-query.md), [`CommentQuery`](comment-query.md), [`SiteQuery` and `NetworkQuery`](site-network-query.md).

- [A builder collects arguments](#a-builder-collects-arguments)
- [Getting the result](#getting-the-result)
- [A common vocabulary](#a-common-vocabulary)
- [Defaults](#defaults)
- [Errors](#errors)
- [Sub-queries: `where()` and `orWhere()`](#sub-queries-where-and-orwhere)
- [Arguments the builders do not cover](#arguments-the-builders-do-not-cover)
- [Reusing a builder](#reusing-a-builder)
- [Adding a parameter](#adding-a-parameter)

## A builder collects arguments

Every query class is a builder: you start it with `select()` (or `find()`), chain methods, and each method stores one WordPress argument. Nothing runs until you ask for a result.

```php
use Pollora\Query\PostQuery;

$query = PostQuery::select()   // new builder
    ->postType('page')         // stores 'post_type'
    ->postParent(5);           // stores 'post_parent'
```

`select()` and `find()` are the two entry points:

| Entry point | Meaning |
|---|---|
| `Class::select()` | An empty builder. |
| `Class::select('ids')` | An empty builder that returns [only IDs](results.md#fields) (the accepted values depend on the class). |
| `Class::find(5)` | A builder restricted to one ID (`p` for posts, `include` for terms and users, `comment__in`, `site__in`, `network__in`). |
| `Class::find([1, 2, 3])` | A builder restricted to several IDs. |

## Getting the result

There are three ways to leave the builder:

| You call | You get |
|---|---|
| `getArguments()` | The plain array of arguments. Nothing is queried: use it to inspect, log, cache or pass to another API. |
| `get()` | The `WP_Query` (or `WP_Term_Query`, `WP_User_Query`…) object, built with those arguments. |
| `first()`, `ids()`, `count()`, `exists()`… | Directly the thing you wanted. See [Getting results](results.md). |

`get()` is `new WP_Query($query->getArguments())`, nothing more:

```php
PostQuery::select()->postType('page')->postParent(5)->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_parent' => 5,
    'post_type' => 'page',
]
```

Every example in this documentation shows the arguments that `getArguments()` returns for the code above it. The keys `fields` and `post_type` come from the [defaults](#defaults).

## A common vocabulary

The same idea has the same method name in every class, whatever the WordPress argument is called.

| Idea | Methods | Examples of WordPress arguments |
|---|---|---|
| Restrict to IDs of the queried object | `whereIn([...])`, `whereNotIn([...])` | `post__in`, `include`, `comment__in`, `site__in`, `network__in` |
| Restrict by another list | `xxxIn([...])`, `xxxNotIn([...])` | `author__in`, `role__in`, `category__not_in`, `domain__in` |
| Number of results | `limit(n)` or `take(n)` | `posts_per_page`, `number` |
| Skip results | `offset(n)` or `skip(n)` | `offset` |
| Page of results | `paged(n)` | `paged` |
| Sorting | `orderBy($field, $direction)`, `orderByXxx()`, `latest()`, `oldest()` | `orderby`, `order` |
| Type of data returned | `fields('ids')` | `fields` |
| Skip the total count | `noFoundRows()` | `no_found_rows` |
| Cache | `cacheResults()`, `updateMetaCache()` | `cache_results`, `update_*_meta_cache` |
| Meta | `metaKey()`, `metaValue()`, `metaQuery()` | `meta_key`, `meta_value`, `meta_query` |
| Dates | `dateQuery()` | `date_query` |

A `Not` or `NotIn` suffix excludes, an `In` suffix is a list. Boolean switches default to `true` when called without an argument: `noFoundRows()` is `noFoundRows(true)`.

## Defaults

A builder sends **only what you asked for**, plus a few defaults chosen so that an unfiltered query returns everything and nothing is silently cut. Each class documents its own; here they are together.

| Class | What the builder sends by default | WordPress' own default | Why |
|---|---|---|---|
| `PostQuery` | `post_type => 'any'`, `fields => 'all'` | `post_type => 'post'` | A query without a type should not hide pages and custom post types. `find(5)` finds a page too. |
| `TermQuery` | `hide_empty => false`, `fields => 'all'` | `hide_empty => true` | A term that has no post yet is not "missing". Call `hideEmpty()` for WordPress' behaviour. |
| `UserQuery` | `fields => 'all'` | no limit | Same as WordPress. |
| `CommentQuery` | nothing | `status => 'all'` (pending and approved: **no spam, no trash**), `no_found_rows => true` | Kept as is on purpose: listing spam by default would be unsafe. Ask for it with `status('spam')`. |
| `SiteQuery` | `number => 0` | `number => 100` | WordPress silently stops at 100 sites. Call `limit()` for a cap. |
| `NetworkQuery` | nothing | no limit | Same as WordPress. |

## Errors

A value that WordPress would silently ignore throws a `Pollora\Query\QueryException` instead, so a typo does not turn into an unfiltered query.

```php
PostQuery::select()->userPermission('writable');
// QueryException: Invalid permission supplied: writable
```

```php
UserQuery::select()->searchColumns(['user_pass']);
// QueryException: Invalid search column supplied: user_pass
```

```php
TermQuery::select()->orderBy('name', 'sideways');
// QueryException: Invalid order direction supplied: SIDEWAYS
```

The checks cover `fields()` values, order directions, `userPermission()`, search columns, the field of a tax clause, the `hierarchical()` mode of comments and the keys of a date array. Free-form values (a meta key, a meta type, a post status) are not checked: WordPress and your database own them.

## Sub-queries: `where()` and `orWhere()`

`taxQuery()`, `metaQuery()` and `dateQuery()` take a closure. It receives a builder on which you add clauses with `where()` (or `andWhere()`, its alias) and `orWhere()`, and the `relation` keys are written for you.

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('status')->equalTo('active'))
            ->orWhere($query->meta('featured')->equalTo('yes'));
    })
    ->get();
```

`WP_Query` receives:

```php
[
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
            'key' => 'featured',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'yes',
        ],
    ],
    'post_type' => 'any',
]
```

### Mixing `where()` and `orWhere()`

WordPress allows a single `relation` per level. When you switch relation on a level that already holds several clauses, they are grouped first, left to right, **as Eloquent does**: `where(a)->where(b)->orWhere(c)` is `(a AND b) OR c`.

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('status')->equalTo('active'))
            ->where($query->meta('stock')->greaterThan(10))
            ->orWhere($query->meta('featured')->equalTo('yes'));
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'OR',
        [
            'relation' => 'AND',
            [
                'key' => 'status',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'active',
            ],
            [
                'key' => 'stock',
                'compare' => '>',
                'type' => 'UNSIGNED',
                'value' => 10,
            ],
        ],
        [
            'key' => 'featured',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'yes',
        ],
    ],
    'post_type' => 'any',
]
```

Reading the output: `relation` is `OR`, its first clause is the group `status AND stock`, and the second is `featured`.

With a single clause, the relation just changes: `orWhere(a)->where(b)` is `a AND b`.

### Nested groups

Pass a closure to `where()` or `orWhere()` to group conditions:

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('status')->equalTo('active'))
            ->where(function (MetaQuery $group) {
                $group
                    ->where($group->meta('stock')->greaterThan(10))
                    ->orWhere($group->meta('backorder')->equalTo('yes'));
            });
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'status',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'active',
        ],
        [
            'relation' => 'OR',
            [
                'key' => 'stock',
                'compare' => '>',
                'type' => 'UNSIGNED',
                'value' => 10,
            ],
            [
                'key' => 'backorder',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'yes',
            ],
        ],
    ],
    'post_type' => 'any',
]
```

That reads `status = active AND (stock > 10 OR backorder = yes)`.

### Calling a sub-query method twice

Each call adds to the previous ones; clauses are never duplicated.

```php
PostQuery::select()
    ->metaQuery(fn (MetaQuery $query) => $query->where($query->meta('a')->equalTo('1')))
    ->metaQuery(fn (MetaQuery $query) => $query->where($query->meta('b')->equalTo('2')))
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'a',
            'compare' => '=',
            'type' => 'NUMERIC',
            'value' => '1',
        ],
        [
            'key' => 'b',
            'compare' => '=',
            'type' => 'NUMERIC',
            'value' => '2',
        ],
    ],
    'post_type' => 'any',
]
```

### A clause without a closure

Each clause builder can also be passed directly, which is handy for a single condition. `Query::meta()`, `Query::tax()` and `Query::date()` create them without a closure:

```php
PostQuery::select()
    ->metaQuery(Query::meta('status')->equalTo('active'))
    ->taxQuery(Query::tax('category')->searchByTermSlug()->contains(['news']))
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'status',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'active',
        ],
    ],
    'post_type' => 'any',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => [
                'news',
            ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]
```

## Arguments the builders do not cover

`setRawArgs()` merges a plain array into the arguments, for an argument added by a plugin or a newer WordPress. **Raw arguments win** over the ones set by methods.

```php
PostQuery::select()
    ->postType('page')
    ->setRawArgs(['ep_integrate' => true, 'post_type' => 'product'])
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'product',
    'ep_integrate' => true,
]
```

## Reusing a builder

Methods that **set** an argument change the builder and return it, so keep a reference only if you mean to build on it:

```php
$base = PostQuery::select()->postType('product')->postStatus('publish');

$base->limit(3);                       // $base now has a limit, whether or not you use the result
$sameBase = $base->limit(5);           // $sameBase and $base are the same object
```

To derive several queries from a base, create it with a function (or `clone` it):

```php
$products = fn () => PostQuery::select()->postType('product')->postStatus('publish');

$latest = $products()->latest()->take(3)->get();
$total  = $products()->count();
```

[Terminal methods](results.md) (`first()`, `ids()`, `count()`, `exists()`…) work on an internal copy and leave the builder untouched, so `$query->count()` followed by `$query->get()` is safe.

## Adding a parameter

The builders turn property names into WordPress arguments with `Str::snake()`; this is what the [wordpress-args](https://github.com/Pollora/wordpress-args) package does. A parameter is a protected property plus a method that sets it:

| Property | Argument |
|---|---|
| `$postType` | `post_type` |
| `$noFoundRows` | `no_found_rows` |
| `$category_In` | `category__in` |
| `$tagSlug_NotIn` | `tag_slug__not_in` |

The underscore before `In` / `NotIn` is what produces the double underscore of the WordPress name. A property left at `null` is not sent.
