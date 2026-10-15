# Getting results

`get()` returns the WordPress query object. Most of the time you want something simpler, so every query class has terminal methods that run the query with the arguments that fit their purpose.

- [Overview](#overview)
- [How terminals behave](#how-terminals-behave)
- [`first()`](#first)
- [`ids()`, `names()`, `slugs()` and `pluck()`](#ids-names-slugs-and-pluck)
- [`count()`](#count)
- [`exists()`](#exists)
- [Fields](#fields)
- [Pagination and limits](#pagination-and-limits)

## Overview

| Method | Returns | `PostQuery` | `TermQuery` | `UserQuery` | `CommentQuery` | `SiteQuery` | `NetworkQuery` |
|---|---|:-:|:-:|:-:|:-:|:-:|:-:|
| `getArguments()` | `array` of arguments | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `get()` | `WP_Query`, `WP_Term_Query`, `WP_User_Query`, `WP_Comment_Query`, `WP_Site_Query`, `WP_Network_Query` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `first()` | the first match, or `null` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `ids()` | `int[]` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `count()` | `int`, the total number of matches | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `exists()` | `bool` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `names()`, `slugs()` | `string[]` | | ✓ | | | | |
| `pluck($field)` | `string[]`, one column | | | ✓ | | | |

```php
use Pollora\Query\PostQuery;

$post   = PostQuery::select()->postType('page')->postSlug('about')->first(); // WP_Post|null
$ids    = PostQuery::select()->postType('post')->limit(10)->ids();           // [42, 41, 39, ...]
$total  = PostQuery::select()->postType('post')->postStatus('publish')->count();
$exists = PostQuery::select()->postType('page')->postSlug('about')->exists();
```

## How terminals behave

A terminal method:

1. works on **a copy** of the builder, so the builder keeps its own arguments and you can call another terminal (or `get()`) afterwards;
2. **overrides only what it needs** (a limit of 1, the `fields`, the count flag) and keeps your filters, order and meta/tax/date queries;
3. **ignores the pagination** that would make the result wrong (see each method).

```php
$query = PostQuery::select()->postType('product')->limit(10)->paged(3);

$query->count();   // total number of products, not 10
$query->first();   // the first product of the query, not the first of page 3
$query->get();     // still the third page of 10 products
```

## `first()`

Runs the query with a limit of 1 and returns the first record, or `null` when nothing matches. It resets the offset and the page, and skips the total count.

| Class | Returns |
|---|---|
| `PostQuery` | `WP_Post`, an ID with `fields('ids')`, a partial object with `fields('id=>parent')` |
| `TermQuery` | `WP_Term`, or the first value of the `fields()` you chose (`'ids'`, `'names'`…). With `fields('count')` it returns a `WP_Term`: a count has no "first". |
| `UserQuery` | `WP_User`, or the first value of the `fields()` you chose |
| `CommentQuery` | `WP_Comment`, an ID with `fields('ids')` |
| `SiteQuery`, `NetworkQuery` | `WP_Site`, `WP_Network`, an ID with `fields('ids')` |

`first()` does not add an order: add one (`latest()`, `orderBy()`…) if "first" must mean something.

## `ids()`, `names()`, `slugs()` and `pluck()`

Return a plain list of scalars, and ask WordPress for that column only (no full objects).

- `ids()` on every class: `int[]`.
- `TermQuery::names()` and `slugs()`: `string[]`.
- `UserQuery::pluck('user_email')`: any single user column among `ID`, `display_name`, `user_login`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_pass`, `user_activation_key`, `user_status` (and `spam`, `deleted` on multisite). `pluck('all')` throws a `QueryException`: use `get()`.

They keep your limit, offset and page: `limit(20)->ids()` returns at most 20 IDs. Without a limit, WordPress' default applies (see [Pagination and limits](#pagination-and-limits)), so call `limit()` or, for posts, `noPaging()` if you need every ID.

## `count()`

Returns the total number of matches, **whatever the limit, offset or page** set on the builder (`found_posts`, a `count` query, `get_total()`… depending on the class). It forces the total to be computed even if you called `noFoundRows()`.

```php
PostQuery::select()->postType('post')->postStatus('publish')->count();
UserQuery::select()->role('subscriber')->count();
CommentQuery::select()->postId(42)->pending()->count();
```

## `exists()`

`true` when at least one record matches. It fetches a single ID, which is cheaper than `count() > 0`.

## Fields

`fields()` controls what a query returns. It validates its value and throws a `QueryException` for anything else.

| Class | Accepted values |
|---|---|
| `PostQuery` | `'all'` (or `'*'`), `'ids'`, `'id=>parent'` |
| `TermQuery` | `'all'` (or `'*'`), `'all_with_object_id'`, `'ids'`, `'tt_ids'`, `'names'`, `'slugs'`, `'count'`, `'id=>parent'`, `'id=>name'`, `'id=>slug'` |
| `UserQuery` | `'all'` (or `'*'`), or one field, or **an array of fields**: `'ID'`, `'display_name'`, `'user_login'`, `'user_nicename'`, `'user_email'`, `'user_url'`, `'user_registered'`, `'user_pass'`, `'user_activation_key'`, `'user_status'`, `'spam'`, `'deleted'` |
| `CommentQuery`, `SiteQuery`, `NetworkQuery` | `'all'` (or `'*'`), `'ids'` |

`select('ids')` is a shortcut for `select()->fields('ids')`:

```php
$ids = PostQuery::select('ids')->postType('page')->get()->posts;      // WP_Query::$posts is int[]
$mails = UserQuery::select(['ID', 'user_email'])->get()->get_results(); // objects with those two fields
```

## Pagination and limits

Each WordPress query has its own limit behaviour, and the builders do not hide it:

| Class | Without `limit()` | Notes |
|---|---|---|
| `PostQuery` | WordPress' *Posts per page* setting (10 by default) | `noPaging()` returns everything. `paged()` selects the page. |
| `TermQuery` | every term | |
| `UserQuery` | every user | `paged()` works with `limit()`. |
| `CommentQuery` | every comment | `noFoundRows` is `true` by default in WordPress: call `noFoundRows(false)` to get `found_comments` and `max_num_pages` from `get()`. |
| `SiteQuery` | every site (`number => 0`, WordPress alone stops at 100) | |
| `NetworkQuery` | every network | |

For terms, users, comments, sites and networks, WordPress applies `offset` only together with a limit: use `skip()` with `limit()`.
