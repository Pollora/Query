# UserQuery

`UserQuery` builds the arguments of [`WP_User_Query`](https://developer.wordpress.org/reference/classes/wp_user_query/).

Read [Concepts](concepts.md) first, and [Getting results](results.md) for the terminal methods. Examples assume:

```php
use Pollora\Query\UserQuery;
```

- [Defaults](#defaults)
- [Roles and capabilities](#roles-and-capabilities)
- [Selecting users](#selecting-users)
- [Search](#search)
- [Authors](#authors)
- [Order](#order)
- [Limit and pagination](#limit-and-pagination)
- [Fields](#fields)
- [Meta and dates](#meta-and-dates)
- [Performance](#performance)
- [Results](#results)

## Defaults

```php
UserQuery::select()->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
]
```

The builder adds no constraint: WordPress returns **every** user, with no limit, and the full `WP_User` objects (`fields => 'all'`).

## Roles and capabilities

| Method | WordPress argument | Matches users… |
|---|---|---|
| `role(string\|array)` | `role` | having **every** listed role |
| `roleIn(array)` | `role__in` | having **at least one** of the roles |
| `roleNotIn(array)` | `role__not_in` | having **none** of the roles |
| `capability(string\|array)` | `capability` | having **every** listed capability |
| `capabilityIn(array)` | `capability__in` | having at least one |
| `capabilityNotIn(array)` | `capability__not_in` | having none |
| `blogId(int)` | `blog_id` | roles and capabilities are read on this site (multisite) |

Capabilities only work if they are stored in the database: those added by a plugin on the fly, or modified by `map_meta_cap`, are not found. `capability('edit_posts')` is the replacement of the deprecated `who => 'authors'`, which `UserQuery` does not support.

```php
UserQuery::select()->roleIn(['editor', 'author'])->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
    'role__in' => [
        'editor',
        'author',
    ],
]
```

## Selecting users

| Method | WordPress argument |
|---|---|
| `UserQuery::find(5)`, `find([1, 2])` | `include` |
| `whereIn(array)` | `include` |
| `whereNotIn(array)` | `exclude` |
| `login(string)` | `login` |
| `loginIn(array)` / `loginNotIn(array)` | `login__in` / `login__not_in` |
| `nicename(string)` | `nicename` |
| `nicenameIn(array)` / `nicenameNotIn(array)` | `nicename__in` / `nicename__not_in` |

An empty `whereIn([])` is **not** "no result": WordPress ignores an empty `include`, so every user matches. Guard an empty list yourself.

## Search

| Method | WordPress argument |
|---|---|
| `search(string)` | `search` |
| `searchColumns(array)` | `search_columns`: among `ID`, `user_login`, `user_email`, `user_url`, `user_nicename`, `display_name`. Another column throws a `QueryException`. |

**Without a `*`, the keyword must match a whole value.** A leading `*`, a trailing `*`, or both, make it a partial match (`*bob`, `bob*`, `*bob*`).

Without `searchColumns()`, WordPress picks the columns from the keyword: an `@` searches the email, a number the login and the ID, a URL the website, anything else the login, website, email, nicename and display name.

```php
UserQuery::select()->search('*smith*')->searchColumns(['display_name', 'user_email'])->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
    'search' => '*smith*',
    'search_columns' => [
        'display_name',
        'user_email',
    ],
]
```

## Authors

`hasPublishedPosts()` keeps the users who have published posts: in every public post type, or in the ones you give.

```php
UserQuery::select()->hasPublishedPosts(['post', 'page'])->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
    'has_published_posts' => [
        'post',
        'page',
    ],
]
```

## Order

`orderBy($field, $direction = 'ASC')` adds a sort key, in call order; it also takes a map of fields and directions. `orderby` is sent as a map, so each field has its own direction.

| Method | Sorts by |
|---|---|
| `orderBy($field, $direction)` | `ID`, `display_name`, `user_login`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `post_count`, `meta_value`, `meta_value_num`, a meta key or a meta clause name |
| `orderByName()` | `display_name` |
| `orderByLogin()` | `user_login` |
| `orderByEmail()` | `user_email` |
| `orderByPostCount($direction = 'DESC')` | `post_count` |
| `latest()` | `user_registered` descending |
| `oldest()` | `user_registered` ascending |
| `orderByInclude()` | `include`: keep the order of `whereIn()` |
| `orderByLoginIn()` | `login__in`: keep the order of `loginIn()` |
| `orderByNicenameIn()` | `nicename__in`: keep the order of `nicenameIn()` |
| `orderByMeta($key, $direction, $numeric)` | `meta_value` or `meta_value_num`, and sets `meta_key` |

An invalid direction throws a `QueryException`.

```php
UserQuery::select()->orderBy('display_name')->orderBy('user_registered', 'DESC')->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
    'orderby' => [
        'display_name' => 'ASC',
        'user_registered' => 'DESC',
    ],
]
```

## Limit and pagination

| Method | WordPress argument |
|---|---|
| `limit(int)`, `take(int)` | `number` |
| `offset(int)`, `skip(int)` | `offset` |
| `paged(int)` | `paged`: the page, used together with `limit()` |

```php
UserQuery::select()->role('subscriber')->limit(20)->paged(3)->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => 'all',
    'role' => 'subscriber',
    'number' => 20,
    'paged' => 3,
]
```

## Fields

`fields()` (or `UserQuery::select(...)`) takes `'all'` (or `'*'`), one field, or an array of fields among `ID`, `display_name`, `user_login`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_pass`, `user_activation_key`, `user_status`, `spam` and `deleted`. `'id'` is read as `'ID'`. An unknown field throws a `QueryException`, and so does `'all'` combined with other fields.

```php
UserQuery::select(['ID', 'user_email'])->role('editor')->get();
```

`WP_User_Query` receives:

```php
[
    'fields' => [
        'ID',
        'user_email',
    ],
    'role' => 'editor',
]
```

## Meta and dates

`metaKey()`, `metaValue()`, `metaQuery()`… work as for posts: see [Meta queries](meta-query.md).

`dateQuery()` filters on the registration date (`user_registered`): see [Date queries](date-query.md). Do not name a column: the default is the right one.

```php
UserQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('plan')->equalTo('pro'));
    })
    ->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-01-01'));
    })
    ->get();
```

`WP_User_Query` receives:

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
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'plan',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'pro',
        ],
    ],
]
```

## Performance

| Method | WordPress argument | Notes |
|---|---|---|
| `cacheResults(bool = true)` | `cache_results` | |
| `countTotal(bool = true)` | `count_total` | Count the total number of users (WordPress does by default). |
| `withoutCount()` | `count_total => false` | Faster when you do not paginate: `get_total()` is then `0`. |

## Results

`get()` returns the `WP_User_Query`: read `->get_results()` and `->get_total()`. The [terminal methods](results.md) are:

```php
$user   = UserQuery::select()->login('admin')->first();                      // WP_User|null
$ids    = UserQuery::select()->role('editor')->ids();                        // [2, 7, 9]
$emails = UserQuery::select()->role('subscriber')->pluck('user_email');      // ['a@x.test', ...]
$total  = UserQuery::select()->role('subscriber')->count();
$has    = UserQuery::select()->roleIn(['editor'])->exists();
```
