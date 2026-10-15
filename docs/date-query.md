# Date queries

`dateQuery()` filters by one or several date ranges, on any date column, and builds the `date_query` argument. It is available on [`PostQuery`](post-query.md), [`UserQuery`](user-query.md), [`CommentQuery`](comment-query.md) and [`SiteQuery`](site-network-query.md).

Examples assume:

```php
use Pollora\Query\DateQuery;
use Pollora\Query\PostQuery;
```

- [A first clause](#a-first-clause)
- [Dates you can give](#dates-you-can-give)
- [Ranges: `before()`, `after()`, `between()`](#ranges-before-after-between)
- [An exact period: `within()`](#an-exact-period-within)
- [Which date: columns](#which-date-columns)
- [Including the boundaries: `inclusive()`](#including-the-boundaries-inclusive)
- [Combining clauses](#combining-clauses)

## A first clause

`dateQuery()` takes a closure. It receives a `DateQuery` builder: start a clause with `date()`, describe the range, and add it with `where()`.

```php
PostQuery::select()
    ->postType('post')
    ->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-01-01'));
    })
    ->get();
```

`WP_Query` receives:

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
    'fields' => 'all',
    'post_type' => 'post',
]
```

A date string becomes a precise date: year, month, day, hour, minute and second, on a 24-hour clock. A date without a time is midnight.

## Dates you can give

| You give | The builder sends |
|---|---|
| `'2024-03-05'`, `'2024-03-05 14:30:00'`, any string `strtotime()` understands (`'last monday'`) | the year, month, day, hour, minute and second of that moment |
| a numeric string, read as a timestamp (`'1709647800'`) | the same, from the timestamp |
| an array `['year' => 2024, 'month' => 3]` | the array, untouched: use it for a partial date |

A string `strtotime()` cannot read throws a `QueryException`.

## Ranges: `before()`, `after()`, `between()`

| Method | Matches |
|---|---|
| `after($date)` | after the date |
| `before($date)` | before the date |
| `between($from, $to)` | after `$from` and before `$to` |

Both can be set on one clause. With an array, only the parts you give are compared:

```php
PostQuery::select()
    ->dateQuery(function (DateQuery $query) {
        $query->where(
            $query->date()->after(['year' => 2021, 'month' => 6])->before('2022-01-01')
        );
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'AND',
        [
            'before' => [
                'year' => 2022,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
            'after' => [
                'year' => 2021,
                'month' => 6,
            ],
        ],
    ],
    'fields' => 'all',
    'post_type' => 'any',
]
```

## An exact period: `within()`

`within()` matches **a period** rather than a range: pass the parts that define it.

```php
PostQuery::select()
    ->postType('post')
    ->dateQuery(fn (DateQuery $query) => $query->where($query->date()->within(['year' => 2024, 'month' => 3])))
    ->get();
```

`WP_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'AND',
        [
            'year' => 2024,
            'month' => 3,
        ],
    ],
    'fields' => 'all',
    'post_type' => 'post',
]
```

With a date string, `within('2024-03-05')` is that **whole day**, while `within('2024-03-05 14:30:00')` is that exact second.

An array with a key other than `year`, `month`, `day`, `hour`, `minute` and `second` throws a `QueryException`.

## Which date: columns

Without a column, WordPress uses the date of the queried table: `post_date` for posts, `user_registered` for users, `comment_date` for comments, `registered` for sites. Nothing is sent by the builder.

| Method | Column |
|---|---|
| `date()` | the default of the table |
| `date('post_modified_gmt')` | the column you name; it must be one WordPress accepts for that table |
| `created()` | `post_date` |
| `modified()` | `post_modified` |
| `registered()` | `user_registered` |

```php
PostQuery::select()
    ->postType('post')
    ->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->modified()->after('2024-01-01'));
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'AND',
        [
            'column' => 'post_modified',
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
    'fields' => 'all',
    'post_type' => 'post',
]
```

## Including the boundaries: `inclusive()`

By default `before()` and `after()` exclude the date itself. `inclusive()` includes it.

- `$query->date()->after('2024-01-01')->inclusive()` applies to **that clause**.
- `$query->inclusive()` applies to **every clause** of the query, including the ones added after it.

WordPress reads `inclusive` on each clause and does not inherit it from the query root, so the builder writes it on every clause for you.

```php
PostQuery::select()
    ->dateQuery(function (DateQuery $query) {
        $query
            ->where($query->date()->after('2024-01-01'))
            ->orWhere($query->date()->before('2023-01-01'))
            ->inclusive();
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'OR',
        [
            'after' => [
                'year' => 2024,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
            'inclusive' => true,
        ],
        [
            'before' => [
                'year' => 2023,
                'month' => 1,
                'day' => 1,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
            'inclusive' => true,
        ],
    ],
    'fields' => 'all',
    'post_type' => 'any',
]
```

## Combining clauses

`where()` and `andWhere()` add a clause with `AND`, `orWhere()` with `OR`, and closures group clauses. See [Concepts](concepts.md#sub-queries-where-and-orwhere).

```php
UserQuery::select()
    ->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->between('2024-01-01', '2024-03-31'));
    })
    ->get();
```

`WP_Query` receives:

```php
[
    'date_query' => [
        'relation' => 'AND',
        [
            'before' => [
                'year' => 2024,
                'month' => 3,
                'day' => 31,
                'hour' => 0,
                'minute' => 0,
                'second' => 0,
            ],
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
    'fields' => 'all',
]
```

## Method reference

On the `DateQuery` builder (the closure argument): `date($column = null)`, `where()`, `andWhere()`, `orWhere()`, `inclusive()`.

On a clause (`DateQueryBuilder`): `after()`, `before()`, `between()`, `within()`, `created()`, `modified()`, `registered()`, `inclusive()`. `extractFromDate($date)` is the conversion they use (a date string or timestamp to an array of parts); you rarely need it.
