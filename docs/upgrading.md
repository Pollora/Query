# Upgrading from 1.0

This page lists what changed since 1.0.3. Most of it fixes arguments that were wrong or ignored; a few changes alter the results of a query that used to run, so read [what to check](#what-to-check) first.

- [What to check](#what-to-check)
- [Fixes](#fixes)
- [New](#new)

## What to check

These changes can alter the results, or the exceptions, of code that worked before.

### `where()` then `orWhere()` is now grouped

On a level that already had several clauses, switching relation used to turn the **whole** group into the new relation, silently. It now groups the clauses so far, like Eloquent:

```php
$query->where($a)->where($b)->orWhere($c);
// before: a OR b OR c
// now:    (a AND b) OR c
```

A chain with a single clause before the switch, or that only uses one of the two, is unchanged. See [Concepts](concepts.md#mixing-where-and-orwhere).

### `inclusive()` now works

`DateQuery::inclusive()` was written on the query root, where WordPress does not read it, so it had no effect. It is now written on each date clause, so **a query that called it becomes inclusive**: `before()` and `after()` now match the boundary date itself. Remove the call if you relied on the exclusive behaviour.

### Hours are on a 24-hour clock

A date string was converted with the 12-hour format, so `'2022-01-01'` became `hour => 12` and `'2022-01-01 15:10:00'` became `hour => 3`. Both are now right (`0` and `15`). If you compensated for it, remove the workaround.

### Invalid values throw a `QueryException`

These used to be silently ignored, and now throw:

| Call | Invalid value |
|---|---|
| `fields()`, `PostQuery::select('x')` | anything but `'*'`, `'all'`, `'ids'`, `'id=>parent'` |
| `userPermission()` | anything but `'readable'` and `'editable'` |
| `orderBy()` | a direction other than `ASC` and `DESC` (case-insensitive) |

### Removed methods

`initQuery()`, `query()` and `genericQuery()` were public by accident (they came from an internal trait and did nothing useful on their own). They are removed from `PostQuery`. Nothing documented used them.

## Fixes

| Before | Now |
|---|---|
| `DateQueryBuilder::within()` always threw a `TypeError`. | It works: [Date queries](date-query.md#an-exact-period-within). |
| A debug `var_export` printed to the output when a date string was converted. | Removed. |
| `date()` without a column sent `'column' => null`. | No column is sent, so WordPress uses the default of the queried table. |
| A numeric (timestamp) string given to `before()` / `after()` threw a `TypeError`. | It is read as a timestamp. |
| `categoryAnd()` sent `category_and`, which WordPress ignores. | It sends `category__and`. |
| `postType(['post', 'page'])` threw a `TypeError`. | Arrays are accepted. |
| `orderBy(['a' => 'ASC'])` and `inRandomOrder()` followed by `orderBy()` or `latest()` threw a `TypeError`. | They work. `orderby` is always sent as an array, and `inRandomOrder()` is `['rand' => 'ASC']`. |
| `ofType()` was ignored (the type was always detected). | It is honoured. |
| The type of a list was detected from its first value: `between(1, 50)` was `BINARY`, `in([])` raised a warning. | One type fits every value: `between(1, 50)` is `UNSIGNED`, `in([])` is `CHAR`. See [Meta queries](meta-query.md#value-types). |
| Calling `metaQuery()`, `taxQuery()` or `dateQuery()` twice repeated the clauses of the first call. | Each call adds its own clauses once. |

## New

- **Query classes**: [`TermQuery`](term-query.md), [`UserQuery`](user-query.md), [`CommentQuery`](comment-query.md), [`SiteQuery` and `NetworkQuery`](site-network-query.md).
- **Terminal methods** on every class: `first()`, `ids()`, `count()` and `exists()`, plus `names()` and `slugs()` on terms and `pluck()` on users. See [Getting results](results.md).
- **`PostQuery` parameters**: `pageId()`, `pagename()`, `title()`, `menuOrder()`; the date fields `year()`, `month()`, `day()`, `hour()`, `minute()`, `second()`, `week()`, `yearMonth()`; `metaKey()`, `metaValue()`, `metaCompare()`, `metaType()`, `metaCompareKey()`, `metaTypeKey()` and `orderByMeta()`; `exactSearch()`, `searchSentence()`, `searchColumns()`; `noFoundRows()`, `suppressFilters()`, `lazyLoadTermMeta()`.
- **Meta clauses**: `regexp()`, `notRegexp()`, `rlike()`, `keyCompare()`, `keyType()`.
- **Tax clauses**: `containsAll()` (`operator => 'AND'`).
- **Dates**: `registered()`, and `inclusive()` on a single clause.
- **`Query::tax()`**, next to `Query::meta()` and `Query::date()`.
- **`setRawArgs()`** documented: [Concepts](concepts.md#arguments-the-builders-do-not-cover).
