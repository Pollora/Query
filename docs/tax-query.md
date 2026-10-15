# Tax queries

`taxQuery()` filters [`PostQuery`](post-query.md) results by terms of any taxonomy, with several conditions combined by `AND` / `OR`. It builds the `tax_query` argument of `WP_Query`.

Examples assume:

```php
use Pollora\Query\PostQuery;
use Pollora\Query\TaxQuery;
```

- [A first clause](#a-first-clause)
- [Choosing the terms](#choosing-the-terms)
- [Matching by slug, name or ID](#matching-by-slug-name-or-id)
- [Child terms](#child-terms)
- [Combining clauses](#combining-clauses)
- [Method reference](#method-reference)

## A first clause

`taxQuery()` takes a closure. It receives a `TaxQuery` builder: start a clause with `taxonomy()`, describe it, and add it with `where()`.

```php
PostQuery::select()
    ->postType('post')
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query->taxonomy('category')->contains([4, 9])
        );
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'post',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => [
                4,
                9,
            ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]
```

Each clause has the keys WordPress expects: `taxonomy`, `field`, `terms`, `operator` and `include_children`.

For a single clause you can skip the closure: `Query::tax('category')->contains([4, 9])` returns the same clause builder, and `taxQuery()` accepts it directly (see [Concepts](concepts.md#a-clause-without-a-closure)).

## Choosing the terms

| Method | WordPress `operator` | Matches posts… |
|---|---|---|
| `contains(array)` | `IN` | having **at least one** of the terms |
| `containsAll(array)` | `AND` | having **all** the terms |
| `notContains(array)` | `NOT IN` | having **none** of the terms |
| `exists()` | `EXISTS` | having **any** term of the taxonomy |
| `notExists()` | `NOT EXISTS` | having **no** term of the taxonomy |

```php
PostQuery::select()
    ->taxQuery(function (TaxQuery $query) {
        $query->where($query->taxonomy('post_tag')->containsAll([12, 15]));
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'any',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => [
                12,
                15,
            ],
            'operator' => 'AND',
            'include_children' => true,
        ],
    ],
]
```

For `exists()` and `notExists()` the terms are irrelevant: WordPress ignores them.

```php
PostQuery::select()
    ->taxQuery(fn (TaxQuery $query) => $query->where($query->taxonomy('post_tag')->notExists()))
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'any',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'post_tag',
            'field' => 'term_id',
            'terms' => null,
            'operator' => 'NOT EXISTS',
            'include_children' => true,
        ],
    ],
]
```

## Matching by slug, name or ID

Terms are matched by `term_id` unless you say otherwise.

| Method | `field` |
|---|---|
| `searchById()` | `term_id` (the default) |
| `searchByTermSlug()` | `slug` |
| `searchByTermName()` | `name` |
| `searchByTermTaxId()` | `term_taxonomy_id` |
| `field('slug')` | any of the above, by name. Other values throw a `QueryException`. |

```php
PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query->taxonomy('product_cat')->searchByTermSlug()->contains(['shoes', 'bags'])
        );
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
                'bags',
            ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]
```

```php
Query::tax('category')->field('title')
// QueryException: Invalid tax field type supplied: title
```

## Child terms

For hierarchical taxonomies, WordPress includes the children of the terms you list. Turn that off with `onlyParent()` or `excludeChildren()` (same effect); `includeChildren()` puts it back.

```php
PostQuery::select()
    ->taxQuery(function (TaxQuery $query) {
        $query->where($query->taxonomy('category')->contains([4])->onlyParent());
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'fields' => 'all',
    'post_type' => 'any',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => [
                4,
            ],
            'operator' => 'IN',
            'include_children' => false,
        ],
    ],
]
```

## Combining clauses

`where()` and `andWhere()` add a clause with `AND`, `orWhere()` with `OR`. Closures group clauses. The rules, including how `where(a)->where(b)->orWhere(c)` is read, are in [Concepts](concepts.md#sub-queries-where-and-orwhere).

```php
PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query
            ->where($query->taxonomy('product_cat')->searchByTermSlug()->contains(['shoes']))
            ->where(function (TaxQuery $group) {
                $group
                    ->where($group->taxonomy('product_tag')->searchByTermSlug()->contains(['sale']))
                    ->orWhere($group->taxonomy('product_tag')->searchByTermSlug()->contains(['clearance']));
            });
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
        [
            'relation' => 'OR',
            [
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => [
                    'sale',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
            [
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => [
                    'clearance',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
        ],
    ],
]
```

That reads `product_cat = shoes AND (product_tag = sale OR product_tag = clearance)`.

## Method reference

On the `TaxQuery` builder (the closure argument): `taxonomy($name)`, `where()`, `andWhere()`, `orWhere()`.

On a clause (`TaxQueryBuilder`): `contains()`, `containsAll()`, `notContains()`, `exists()`, `notExists()`, `field()`, `searchById()`, `searchByTermSlug()`, `searchByTermName()`, `searchByTermTaxId()`, `includeChildren()`, `onlyParent()`, `excludeChildren()`.
