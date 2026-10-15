# TermQuery

`TermQuery` builds the arguments of [`WP_Term_Query`](https://developer.wordpress.org/reference/classes/wp_term_query/): categories, tags and the terms of any custom taxonomy.

Read [Concepts](concepts.md) first, and [Getting results](results.md) for the terminal methods. Examples assume:

```php
use Pollora\Query\TermQuery;
```

- [Defaults](#defaults)
- [Choosing the taxonomy](#choosing-the-taxonomy)
- [Selecting terms](#selecting-terms)
- [Hierarchy](#hierarchy)
- [Search](#search)
- [Order](#order)
- [Limit](#limit)
- [Fields](#fields)
- [Meta](#meta)
- [Cache](#cache)
- [Results](#results)

## Defaults

```php
TermQuery::select()->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'all',
    'hide_empty' => false,
]
```

- **`hide_empty` is `false`.** WordPress hides the terms that have no post by default, which makes a new term "disappear". The builder returns every term; call `hideEmpty()` to get WordPress' behaviour back.
- `fields` is `'all'` (full `WP_Term` objects).
- Without `taxonomy()`, WordPress queries **every** taxonomy.

## Choosing the taxonomy

| Method | WordPress argument |
|---|---|
| `taxonomy(string\|array)` | `taxonomy` |
| `objectIds(int\|array)` | `object_ids`: only the terms attached to these objects (posts, users…) |

```php
TermQuery::select()->taxonomy('category')->objectIds(42)->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'all',
    'taxonomy' => 'category',
    'object_ids' => 42,
    'hide_empty' => false,
]
```

## Selecting terms

| Method | WordPress argument | Notes |
|---|---|---|
| `TermQuery::find(5)`, `find([1, 2])` | `include` | By term ID. |
| `whereIn(array)` | `include` | |
| `whereNotIn(array)` | `exclude` | Ignored by WordPress when `whereIn()` is used. |
| `excludeTree(array)` | `exclude_tree` | Excludes the terms **and all their descendants**. |
| `name(string\|array)` | `name` | Exact name(s). |
| `slug(string\|array)` | `slug` | Exact slug(s). |
| `termTaxonomyId(int\|array)` | `term_taxonomy_id` | |
| `hideEmpty(bool = true)` | `hide_empty` | Hide the terms with no post. |
| `withEmpty()` | `hide_empty => false` | The default. |

An empty `whereIn([])` is **not** "no result": WordPress ignores an empty `include`, so every term matches. Guard an empty list yourself.

## Hierarchy

| Method | WordPress argument | Notes |
|---|---|---|
| `rootOnly()` | `parent => 0` | Top-level terms. |
| `parentId(int)` | `parent` | The direct children of a term. |
| `childOf(int)` | `child_of` | **All** the descendants of a term. Ignored when several taxonomies are queried. |
| `childless(bool = true)` | `childless` | Terms that have no children. |
| `hierarchical(bool = true)` | `hierarchical` | With `hideEmpty()`, keep the terms whose descendants are not empty. |
| `padCounts(bool = true)` | `pad_counts` | Add the children's count to each term's `count`. |
| `unrestricted()` | `get => 'all'` | Terms regardless of ancestry or emptiness. |

WordPress applies some overrides that the builder does not hide: `unrestricted()` makes it ignore `childOf()`, `childless()`, `hideEmpty()`, `hierarchical()` and `padCounts()`; a non-zero `parentId()` makes it ignore `childOf()`.

```php
TermQuery::select()->taxonomy('category')->parentId(12)->hideEmpty()->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'all',
    'taxonomy' => 'category',
    'hide_empty' => true,
    'parent' => 12,
]
```

## Search

| Method | WordPress argument | Matches |
|---|---|---|
| `search(string)` | `search` | the keyword in the name or the slug (contains) |
| `nameLike(string)` | `name__like` | names containing the text |
| `descriptionLike(string)` | `description__like` | descriptions containing the text |

## Order

A term query sorts by **one** field, unlike posts: `orderBy()` replaces the previous sort, and takes a direction (`ASC` by default, as in WordPress).

| Method | `orderby` / `order` |
|---|---|
| `orderBy($field, $direction = 'ASC')` | any term field (`name`, `slug`, `term_id`, `description`, `parent`, `term_group`…), `count`, a meta key or a meta clause name |
| `orderByName($direction = 'ASC')` | `name` |
| `orderByCount($direction = 'DESC')` | `count`: the most used first |
| `orderByInclude()` | `include`: keep the order of `whereIn()`. Ignored by WordPress if `whereIn()` is empty. |
| `orderBySlugIn()` | `slug__in`: keep the order of `slug([...])`. Ignored unless `slug()` received an array. |
| `orderByMeta($key, $direction, $numeric)` | `meta_value` or `meta_value_num`, and sets `meta_key` |
| `withoutOrder()` | `none`: no `ORDER BY` |

An invalid direction throws a `QueryException`.

```php
TermQuery::select()->taxonomy('post_tag')->orderByCount()->limit(10)->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'all',
    'taxonomy' => 'post_tag',
    'hide_empty' => false,
    'number' => 10,
    'orderby' => 'count',
    'order' => 'DESC',
]
```

## Limit

`limit()` / `take()` (`number`) and `offset()` / `skip()` (`offset`). Without a limit WordPress returns every term, and it applies `offset` only together with a limit.

## Fields

`fields()` (or `TermQuery::select('ids')`): `'all'` (or `'*'`), `'all_with_object_id'`, `'ids'`, `'tt_ids'`, `'names'`, `'slugs'`, `'count'`, `'id=>parent'`, `'id=>name'`, `'id=>slug'`. Anything else throws a `QueryException`.

```php
TermQuery::select('id=>name')->taxonomy('category')->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'id=>name',
    'taxonomy' => 'category',
    'hide_empty' => false,
]
```

## Meta

`metaKey()`, `metaValue()`, `metaCompare()`, `metaType()`, `metaCompareKey()`, `metaTypeKey()` and `metaQuery()` work as for posts: see [Meta queries](meta-query.md).

```php
TermQuery::select()
    ->taxonomy('product_cat')
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('featured')->equalTo('yes'));
    })
    ->get();
```

`WP_Term_Query` receives:

```php
[
    'fields' => 'all',
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'featured',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'yes',
        ],
    ],
]
```

## Cache

| Method | WordPress argument |
|---|---|
| `cacheResults(bool = true)` | `cache_results` |
| `updateMetaCache(bool = true)` | `update_term_meta_cache` |
| `cacheDomain(string)` | `cache_domain` |

## Results

`get()` returns the `WP_Term_Query` (its terms are in `->terms`). The [terminal methods](results.md) are:

```php
$term  = TermQuery::select()->taxonomy('category')->slug('news')->first();    // WP_Term|null
$ids   = TermQuery::select()->taxonomy('category')->rootOnly()->ids();        // [3, 8, 14]
$names = TermQuery::select()->taxonomy('post_tag')->orderByCount()->limit(5)->names(); // ['php', ...]
$slugs = TermQuery::select()->taxonomy('post_tag')->slugs();
$total = TermQuery::select()->taxonomy('category')->hideEmpty()->count();
$has   = TermQuery::select()->taxonomy('category')->slug('news')->exists();
```
