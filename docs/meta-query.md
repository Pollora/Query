# Meta queries

`metaQuery()` filters by custom fields (post meta, term meta, user meta, comment meta, site meta), with several conditions combined by `AND` / `OR`. It builds the `meta_query` argument.

It is available on [`PostQuery`](post-query.md), [`TermQuery`](term-query.md), [`UserQuery`](user-query.md), [`CommentQuery`](comment-query.md) and [`SiteQuery`](site-network-query.md), with the same methods. Examples assume:

```php
use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;
```

- [A first clause](#a-first-clause)
- [Comparisons](#comparisons)
- [Value types](#value-types)
- [Combining clauses](#combining-clauses)
- [Named clauses and sorting](#named-clauses-and-sorting)
- [Comparing the key](#comparing-the-key)
- [The simple top-level arguments](#the-simple-top-level-arguments)

## A first clause

`metaQuery()` takes a closure. It receives a `MetaQuery` builder: start a clause with `meta($key)`, add a comparison, and add the clause with `where()`.

```php
PostQuery::select()
    ->postType('product')
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('status')->equalTo('active'));
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
    ],
    'post_type' => 'product',
]
```

Always finish a clause with a comparison. `meta('status')` alone has no `compare` and no `value`.

For a single clause you can skip the closure: `Query::meta('status')->equalTo('active')` returns the same clause builder, and `metaQuery()` accepts it directly (see [Concepts](concepts.md#a-clause-without-a-closure)).

## Comparisons

| Method | `compare` | Notes |
|---|---|---|
| `equalTo($value)` | `=` | |
| `notEqualTo($value)` | `!=` | |
| `greaterThan($value)` | `>` | |
| `greaterOrEqualTo($value)` | `>=` | |
| `lessThan($value)` | `<` | |
| `lessOrEqualTo($value)` | `<=` | |
| `between($min, $max)` | `BETWEEN` | The value is `[$min, $max]`. |
| `notBetween($min, $max)` | `NOT BETWEEN` | |
| `in(array)` | `IN` | |
| `notIn(array)` | `NOT IN` | |
| `like(string)` | `LIKE` | **Contains.** WordPress wraps the value in `%…%` itself and escapes any `%` or `_` you give: pass the bare text. |
| `notLike(string)` | `NOT LIKE` | |
| `regexp(string)` | `REGEXP` | |
| `notRegexp(string)` | `NOT REGEXP` | |
| `rlike(string)` | `RLIKE` | |
| `exists()` | `EXISTS` | The key is set, whatever its value. No value is sent. |
| `notExists()` | `NOT EXISTS` | The key is not set. |

```php
PostQuery::select()
    ->postType('product')
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('price')->between(10, 50))
            ->where($query->meta('color')->in(['red', 'blue']))
            ->where($query->meta('discontinued')->notExists());
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
            'key' => 'price',
            'compare' => 'BETWEEN',
            'type' => 'UNSIGNED',
            'value' => [
                10,
                50,
            ],
        ],
        [
            'key' => 'color',
            'compare' => 'IN',
            'type' => 'CHAR',
            'value' => [
                'red',
                'blue',
            ],
        ],
        [
            'key' => 'discontinued',
            'compare' => 'NOT EXISTS',
            'type' => 'CHAR',
        ],
    ],
    'post_type' => 'product',
]
```

## Value types

WordPress compares meta values as text unless a `type` is given. The builder **detects the type from the value**, so `greaterThan(10)` compares numbers, not strings.

| Value | Type sent |
|---|---|
| `0` or `1` (integers) | `BINARY` |
| a negative integer | `SIGNED` |
| any other integer | `UNSIGNED` |
| a float | `DECIMAL` |
| a numeric string (`'42'`) | `NUMERIC` |
| `'2024-03-05'` | `DATE` |
| `'14:30:00'` | `TIME` |
| `'2024-03-05 14:30:00'` | `DATETIME` |
| `null`, any other string, a boolean | `CHAR` |

For a list (`in()`, `between()`…), one type is chosen so that **every** value fits: the common type when they agree, otherwise the widest numeric one (`DECIMAL`, then `NUMERIC`, then `SIGNED`, then `UNSIGNED`), otherwise `CHAR`. An empty list is `CHAR`. So `between(1, 50)` is `UNSIGNED` and `between(-5, 5)` is `SIGNED`.

**`BINARY` compares as text.** For `0` and `1` it is fine with `equalTo()`, but if you use `greaterThan(0)` on a numeric field, force the type:

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('stock')->ofType('NUMERIC')->greaterThan(0));
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
            'key' => 'stock',
            'compare' => '>',
            'type' => 'NUMERIC',
            'value' => 0,
        ],
    ],
    'post_type' => 'any',
]
```

`ofType($type)` always wins over detection, in any order relative to the comparison. The values WordPress accepts are `NUMERIC`, `BINARY`, `CHAR`, `DATE`, `DATETIME`, `DECIMAL`, `SIGNED`, `TIME` and `UNSIGNED`; the builder does not check them.

## Combining clauses

`where()` and `andWhere()` add a clause with `AND`, `orWhere()` with `OR`, and closures group clauses. See [Concepts](concepts.md#sub-queries-where-and-orwhere) for how `where(a)->where(b)->orWhere(c)` is grouped.

```php
PostQuery::select()
    ->postType('product')
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where($query->meta('status')->equalTo('active'))
            ->where(function (MetaQuery $group) {
                $group
                    ->where($group->meta('stock')->ofType('NUMERIC')->greaterThan(0))
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
                'type' => 'NUMERIC',
                'value' => 0,
            ],
            [
                'key' => 'backorder',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'yes',
            ],
        ],
    ],
    'post_type' => 'product',
]
```

## Named clauses and sorting

`state($name)` gives a clause a name. It becomes the key of the clause in the query, and you can then **sort by it** with `orderBy()`, which is how you sort by a value that is also filtered.

```php
PostQuery::select()
    ->postType('event')
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('starts_at')->state('start')->greaterOrEqualTo('2024-06-01'));
    })
    ->orderBy('start', 'ASC')
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'AND',
        'start' => [
            'key' => 'starts_at',
            'compare' => '>=',
            'type' => 'DATE',
            'value' => '2024-06-01',
        ],
    ],
    'orderby' => [
        'start' => 'ASC',
    ],
    'post_type' => 'event',
]
```

To sort by a custom field without filtering on a value, use `orderByMeta($key)` (or `orderBy('meta_value')` with `metaKey()`), described in [PostQuery](post-query.md#order).

## Comparing the key

By default the key must match exactly. `keyCompare()` changes how the key itself is compared, and `keyType()` the type it is cast to. With `LIKE`, as for values, WordPress matches keys that **contain** the text: this finds every field whose key contains `attr_`.

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('attr_')->keyCompare('LIKE')->exists());
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
            'key' => 'attr_',
            'compare_key' => 'LIKE',
            'compare' => 'EXISTS',
            'type' => 'CHAR',
        ],
    ],
    'post_type' => 'any',
]
```

WordPress accepts `=`, `!=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN`, `EXISTS`, `NOT EXISTS`, `REGEXP`, `NOT REGEXP` and `RLIKE` for `keyCompare()`.

## The simple top-level arguments

When a single condition is enough, no closure is needed: `metaKey()`, `metaValue()`, `metaCompare()`, `metaType()`, `metaCompareKey()` and `metaTypeKey()` set the `meta_key`, `meta_value`, `meta_compare`, `meta_type`, `meta_compare_key` and `meta_type_key` arguments directly. They are available on the same classes as `metaQuery()`.

```php
PostQuery::select()->postType('product')->metaKey('color')->metaValue('red')->metaCompare('!=')->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'meta_key' => 'color',
    'meta_value' => 'red',
    'meta_compare' => '!=',
    'post_type' => 'product',
]
```

Sorting by a meta field relies on these arguments: `orderByMeta('price', 'ASC', numeric: true)` sets `meta_key` and sorts by `meta_value_num`.

## Method reference

On the `MetaQuery` builder (the closure argument): `meta($key)`, `where()`, `andWhere()`, `orWhere()`.

On a clause (`MetaQueryBuilder`): `equalTo()`, `notEqualTo()`, `greaterThan()`, `greaterOrEqualTo()`, `lessThan()`, `lessOrEqualTo()`, `between()`, `notBetween()`, `in()`, `notIn()`, `like()`, `notLike()`, `regexp()`, `notRegexp()`, `rlike()`, `exists()`, `notExists()`, `ofType()`, `state()`, `keyCompare()`, `keyType()`.
