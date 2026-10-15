# SiteQuery and NetworkQuery

`SiteQuery` and `NetworkQuery` build the arguments of [`WP_Site_Query`](https://developer.wordpress.org/reference/classes/wp_site_query/) and [`WP_Network_Query`](https://developer.wordpress.org/reference/classes/wp_network_query/). They are for **multisite** installations.

Read [Concepts](concepts.md) first, and [Getting results](results.md) for the terminal methods. Examples assume:

```php
use Pollora\Query\NetworkQuery;
use Pollora\Query\SiteQuery;
```

- [SiteQuery](#sitequery)
  - [Defaults](#defaults)
  - [Selecting sites](#selecting-sites)
  - [Status](#status)
  - [Search](#search)
  - [Order](#order)
  - [Meta and dates](#meta-and-dates)
  - [Cache](#cache)
- [NetworkQuery](#networkquery)
- [Limit, fields and results](#limit-fields-and-results)

## SiteQuery

### Defaults

```php
SiteQuery::select()->get();
```

`WP_Site_Query` receives:

```php
[
    'number' => 0,
]
```

`number` is `0`: **no limit**. `WP_Site_Query` alone returns at most 100 sites and does not tell you, which silently truncates a large network. Call `limit()` if you want a cap. WordPress applies `offset` only together with a limit, so use `skip()` with `limit()`.

Archived, spam and deleted sites are **returned** unless you filter them (see [Status](#status)).

### Selecting sites

| Method | WordPress argument | Notes |
|---|---|---|
| `SiteQuery::find(3)`, `find([1, 2])` | `site__in` | WordPress' `ID` argument is the same thing. |
| `whereIn(array)` | `site__in` | |
| `whereNotIn(array)` | `site__not_in` | |
| `networkId(int)` | `network_id` | One network. WordPress queries every network when it is `0` or unset. |
| `networkIn(array)` / `networkNotIn(array)` | `network__in` / `network__not_in` | |
| `domain(string)` | `domain` | |
| `domainIn(array)` / `domainNotIn(array)` | `domain__in` / `domain__not_in` | |
| `path(string)` | `path` | |
| `pathIn(array)` / `pathNotIn(array)` | `path__in` / `path__not_in` | |
| `langId(int)` | `lang_id` | |
| `langIn(array)` / `langNotIn(array)` | `lang__in` / `lang__not_in` | |

```php
SiteQuery::select()->networkId(1)->domainIn(['a.example.com', 'b.example.com'])->get();
```

`WP_Site_Query` receives:

```php
[
    'network_id' => 1,
    'domain__in' => [
        'a.example.com',
        'b.example.com',
    ],
    'number' => 0,
]
```

### Status

Each takes a `bool` (default `true`) and is sent as `1` or `0`. Without them WordPress applies no filter.

| Method | WordPress argument |
|---|---|
| `wherePublic(bool = true)` | `public` |
| `whereArchived(bool = true)` | `archived` |
| `whereMature(bool = true)` | `mature` |
| `whereSpam(bool = true)` | `spam` |
| `whereDeleted(bool = true)` | `deleted` |

```php
SiteQuery::select()->wherePublic()->whereArchived(false)->whereSpam(false)->whereDeleted(false)->get();
```

`WP_Site_Query` receives:

```php
[
    'public' => 1,
    'archived' => 0,
    'spam' => 0,
    'deleted' => 0,
    'number' => 0,
]
```

### Search

| Method | WordPress argument |
|---|---|
| `search(string)` | `search`: sites whose domain or path **contains** the keyword. A `*` inside it is a wildcard. |
| `searchColumns(array)` | `search_columns`: `'domain'` and/or `'path'`. Another column throws a `QueryException`. |

### Order

`orderBy($field, $direction = 'ASC')` adds a sort key, in call order; it also takes a map of fields and directions.

| Method | Sorts by |
|---|---|
| `orderBy($field, $direction)` | `id`, `domain`, `path`, `network_id`, `last_updated`, `registered`, `domain_length`, `path_length`, `site__in`, `network__in`, `deleted`, `mature`, `spam`, `archived`, `public` |
| `orderByDomain()`, `orderByPath()` | `domain`, `path` |
| `orderByDomainLength()`, `orderByPathLength()` | `domain_length`, `path_length` |
| `orderByLastUpdated($direction = 'DESC')` | `last_updated` |
| `latest()` / `oldest()` | `registered` descending / ascending |
| `orderByInclude()` | `site__in`: keep the order of `whereIn()` |
| `orderByNetworkIn()` | `network__in`: keep the order of `networkIn()` |
| `orderByMeta($key, $direction, $numeric)` | `meta_value` or `meta_value_num`, and sets `meta_key` |
| `withoutOrder()` | no `ORDER BY` (`'none'`) |

An invalid direction throws a `QueryException`.

```php
SiteQuery::select()->wherePublic()->latest()->limit(10)->get();
```

`WP_Site_Query` receives:

```php
[
    'public' => 1,
    'number' => 10,
    'orderby' => [
        'registered' => 'DESC',
    ],
]
```

### Meta and dates

`metaKey()`, `metaValue()`, `metaQuery()`… work as for posts: see [Meta queries](meta-query.md). `dateQuery()` filters on the registration date (`registered`): see [Date queries](date-query.md). Do not name a column: the default is the right one.

### Cache

| Method | WordPress argument | Notes |
|---|---|---|
| `noFoundRows(bool = true)` | `no_found_rows` | WordPress skips the total by default: call `noFoundRows(false)` to get `found_sites` and `max_num_pages`. |
| `updateCache(bool = true)` | `update_site_cache` | |
| `updateMetaCache(bool = true)` | `update_site_meta_cache` | |

## NetworkQuery

`NetworkQuery` has fewer parameters, and no meta or date queries, as `WP_Network_Query` does not support them. The builder sends nothing by default: WordPress returns every network.

| Method | WordPress argument | Notes |
|---|---|---|
| `NetworkQuery::find(2)`, `find([1, 2])` | `network__in` | |
| `whereIn(array)` / `whereNotIn(array)` | `network__in` / `network__not_in` | |
| `domain()`, `domainIn()`, `domainNotIn()` | `domain`, `domain__in`, `domain__not_in` | |
| `path()`, `pathIn()`, `pathNotIn()` | `path`, `path__in`, `path__not_in` | |
| `search(string)` | `search` | Domain or path **contains** the keyword. A `*` inside it is a wildcard. |
| `orderBy($field, $direction)` | `orderby` | `id`, `domain`, `path`, `domain_length`, `path_length`, `network__in`. Also `orderByDomain()`, `orderByPath()`, `orderByDomainLength()`, `orderByPathLength()`, `orderByInclude()`, `withoutOrder()`. |
| `noFoundRows(bool = true)` | `no_found_rows` | |
| `updateCache(bool = true)` | `update_network_cache` | |

```php
NetworkQuery::select()->domain('example.com')->orderByDomain()->get();
```

`WP_Network_Query` receives:

```php
[
    'domain' => 'example.com',
    'orderby' => [
        'domain' => 'ASC',
    ],
]
```

## Limit, fields and results

Both classes share:

| Method | WordPress argument |
|---|---|
| `limit(int)`, `take(int)` | `number` |
| `offset(int)`, `skip(int)` | `offset` (applied only with a limit) |
| `fields('ids')`, `select('ids')` | `fields`: `'ids'`, or `'all'` / `'*'` for whole objects. Anything else throws a `QueryException`. |

`get()` returns the `WP_Site_Query` (sites in `->sites`) or the `WP_Network_Query` (networks in `->networks`). The [terminal methods](results.md) are:

```php
$site   = SiteQuery::select()->domain('shop.example.com')->first();      // WP_Site|null
$ids    = SiteQuery::select()->networkId(1)->wherePublic()->ids();
$total  = SiteQuery::select()->whereSpam(false)->count();
$has    = NetworkQuery::select()->domain('example.com')->exists();
```
