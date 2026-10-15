# Changelog

All notable changes to this package are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the package follows [Semantic Versioning](https://semver.org/).

Earlier releases (1.0 to 1.0.3) are listed on the [releases page](https://github.com/Pollora/Query/releases).

## [2.0.0] - 2026-10-15

A major release: Query now covers every query class of WordPress, and several arguments that were wrong or silently ignored are fixed. A few fixes change the results of a query that used to run, so read **[Upgrading](docs/upgrading.md)** before updating.

### Added

- **Query classes**: [`TermQuery`](docs/term-query.md) (`WP_Term_Query`), [`UserQuery`](docs/user-query.md) (`WP_User_Query`), [`CommentQuery`](docs/comment-query.md) (`WP_Comment_Query`), [`SiteQuery`](docs/site-network-query.md) (`WP_Site_Query`) and [`NetworkQuery`](docs/site-network-query.md) (`WP_Network_Query`). Every documented argument is covered.
- **Terminal methods**, on every class: `first()`, `ids()`, `count()` and `exists()`, which run on a copy of the builder and leave it untouched. `TermQuery` adds `names()` and `slugs()`, `UserQuery` adds `pluck()`. See [Getting results](docs/results.md).
- **`PostQuery` arguments**: `pageId()`, `pagename()`, `title()`, `menuOrder()`; the date fields `year()`, `month()`, `day()`, `hour()`, `minute()`, `second()`, `week()` and `yearMonth()`; the top-level meta arguments `metaKey()`, `metaValue()`, `metaCompare()`, `metaType()`, `metaCompareKey()` and `metaTypeKey()`; `orderByMeta()`; `exactSearch()`, `searchSentence()` and `searchColumns()`; `noFoundRows()`, `suppressFilters()` and `lazyLoadTermMeta()`.
- **Meta clauses**: `regexp()`, `notRegexp()`, `rlike()`, `keyCompare()` and `keyType()`.
- **Tax clauses**: `containsAll()` (`operator => 'AND'`).
- **Date clauses**: `registered()`, and `inclusive()` on a single clause.
- `Query::tax()`, next to `Query::meta()` and `Query::date()`.
- **Documentation** split into pages under [`docs/`](docs/concepts.md), with a page per query class. Its examples are run by the test suite, and CI fails when one no longer matches the code.

### Changed

- **`where()` followed by `orWhere()` is grouped like Eloquent.** `where(a)->where(b)->orWhere(c)` is `(a AND b) OR c`; it used to turn the whole group into `a OR b OR c`.
- **`DateQuery::inclusive()` works.** It was written where WordPress does not read it, so it had no effect; it is now written on each date clause.
- **Dates use a 24-hour clock.** A date string was converted with the 12-hour format (`'2022-01-01'` gave `hour => 12`, `'… 15:10:00'` gave `hour => 3`). The parts of a converted date are now integers.
- **Invalid values throw a `QueryException`** instead of being silently ignored: `fields()`, `userPermission()` and the direction of `orderBy()`.
- **`date()` without a column no longer sends one**, so WordPress uses the default of the queried table (`post_date`, `user_registered`, `comment_date`, `registered`).
- **Defaults chosen so that nothing is silently left out**: `TermQuery` returns empty terms (`hide_empty => false`) and `SiteQuery` has no cap of 100 sites (`number => 0`). `PostQuery` keeps `post_type => 'any'`. `CommentQuery` keeps WordPress' own defaults on purpose (no spam, no trash). See [Concepts](docs/concepts.md#defaults).

### Fixed

- `DateQueryBuilder::within()` always threw a `TypeError`.
- A debug `var_export` printed to the output when a date string was converted.
- A numeric (timestamp) string given to `before()` or `after()` threw a `TypeError`.
- `categoryAnd()` sent `category_and`, which WordPress ignores; it now sends `category__and`.
- `postType()` did not accept an array.
- `orderBy()` with an array, and `inRandomOrder()` followed by `orderBy()` or `latest()`, threw a `TypeError`.
- `ofType()` was ignored, and the type of a list was detected from its first value only (`between(1, 50)` was `BINARY`, `in([])` raised a warning). One type now fits every value.
- Calling `metaQuery()`, `taxQuery()` or `dateQuery()` twice repeated the clauses of the first call.

### Removed

- The public `initQuery()`, `query()` and `genericQuery()` methods of the query classes. They came from an internal trait by accident and were not documented.

[2.0.0]: https://github.com/Pollora/Query/compare/1.0.3...2.0.0
