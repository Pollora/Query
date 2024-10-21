# PostQuery Class

The `PostQuery` class is a fluent interface for constructing WP_Query objects in WordPress. This class simplifies the process of building complex queries and provides a more readable and concise way to define query parameters.

- [Advantages](#advantages-of-using-postquery-wrapper)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Author](#author)
- [Category](#category)
- [Tag](#tag)
- [Tax_Query](#tax_query)
- [Search](#search)
- [Post](#post)
- [Password](#password)
- [Status](#status)
- [Comment Parameter](#comment-parameter)
- [Pagination](#pagination)
- [Order](#order)
- [Date](#date)
- [Meta Query](#meta-query)
- [Permission](#permission)
- [Mimetype](#mimetype)
- [Cache](#cache)


## Advantages of Using `PostQuery` Wrapper

When using the `PostQuery` wrapper, you gain several advantages over using the original `WP_Query` class for querying posts in WordPress:

1. **Method Chaining**: The `PostQuery` wrapper allows for method chaining, making it easier to construct and read complex queries in a more fluent and readable manner.

2. **Type Safety**: `PostQuery` provides type-hinted methods, reducing the risk of runtime errors by ensuring that the correct data types are used for query parameters.

3. **Readable and Expressive**: The methods in `PostQuery` have descriptive names, making it easier to understand the purpose of each query parameter.

4. **Consistency**: `PostQuery` enforces consistent naming conventions and provides a standardized way to query posts, enhancing code maintainability.

5. **Improved Code Structure**: Using `PostQuery` promotes cleaner and more organized code, separating query logic from the rest of your application.

6. **Reduced Boilerplate**: The wrapper simplifies common tasks, reducing the amount of boilerplate code needed to create queries.

6. **IDE Autocompletion:** Unlike array-based arguments, the `PostQuery` wrapper provides autocompletion support in IDEs, which can significantly improve developer productivity and reduce coding errors.

### Comprehensive Example

Let's consider an example where we want to query posts with specific criteria using the `PostQuery` wrapper and compare it to the equivalent `WP_Query` code. We'll combine various methods to construct a complex query.

#### Using `PostQuery` Wrapper

```php
use Pollora\Query\PostQuery;

$results = PostQuery::select()
    ->postType('product')
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query
                ->taxonomy('category')
                ->contains(['tee-shirt', 'sportswear'])
                ->searchByTermSlug()
        );
    })
    ->dateQuery(function (DateQuery $query) {
        $query->where(
            $query
                ->date('posted_at')
                ->between('2021-01-01', '2022-02-01')
        )->orWhere(
            $query->date()->created()->after('2021-01-01')
        );
    })
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query->meta('status')->equalTo('active')
            )->orWhere(
                $query->meta('highlighted')->equalTo(0)
            );
    })
    ->userPermission('readable')
    ->cacheResults()
    ->get();
```

#### Equivalent `WP_Query` Code

```php
new WP_Query([
    'cache_results' => true,
    'date_query' => [
        'relation' => 'OR',
        [
            'column' => 'posted_at',
            'before' =>
                [
                    'year' => '2022',
                    'month' => '02',
                    'day' => '01',
                ],
            'after' =>
                [
                    'year' => '2021',
                    'month' => '01',
                    'day' => '01',
                ],
        ],
        [
            'column' => 'post_date',
            'after' =>
                [
                    'year' => '2021',
                    'month' => '01',
                    'day' => '01',
                ],
        ],
    ],
    'fields' => 'all',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'status',
            'compare' => '=',
            'value' => 'active',
        ],
        [
            'key' => 'highlighted',
            'compare' => '=',
            'type' => 'BINARY',
            'value' => 0,
        ],
    ],
    'perm' => 'readable',
    'post_type' => 'product',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' =>
                [
                    'tee-shirt',
                    'sportswear',
                ],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]);
```

The table of our good old Wp_Query is a little complex, isn't it? :)

## Installation

To use the `PostQuery` wrapper in your WordPress project, you need to install the `pollora/query` package using Composer.

If you're not using a WordPress environment that integrate composer, you can follow the steps described [in this article](https://deliciousbrains.com/storing-wordpress-in-git/).

To install Pollora Query, run the following command :

```bash
composer require pollora/query
```

Composer will download and install the `pollora/query` package and its dependencies.

### Using the `PostQuery` Wrapper

Once you've installed the `pollora/query` package and set up Composer in your WordPress environment, you can use the `PostQuery` wrapper to construct and execute WordPress post queries as described in the previous sections.

Now, you're ready to harness the power and simplicity of the `PostQuery` wrapper in your WordPress project to streamline your post queries and improve code readability and maintainability.

## Basic Usage

To get started with the `PostQuery` class, you can use the `select()` method to specify the fields you want to retrieve and the `get()` method to generate a WP_Query object with the corresponding arguments.

By default, the `PostQuery` class adopts a more intuitive approach when it comes to querying posts by post type. Unlike the default behavior of WordPress's `WP_Query`, which sets the `post_type` parameter to `'post'` if not specified, the `PostQuery` class assumes a broader scope. It sets the `post_type` parameter to `'all'` by default, indicating that it will search across all post types. This default behavior aligns with a more logical and inclusive approach, ensuring that you can effortlessly query posts of any type without needing to explicitly specify the post type every time you create a query. This simplifies the process and enhances flexibility when constructing your queries.

### Retrieve All Fields

```php
PostQuery::select()->get(); // Equivalent to PostQuery::select('*')->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'fields' => 'all',
    'post_type' => 'any',
]);
```

In this case, the `fields` argument is set to `'all'`, indicating that all fields should be retrieved.

### Specify Specific Fields

You can use the `select()` method with specific field names to tailor your query to your needs.

```php
PostQuery::select('id=>parent')->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'fields' => 'id=>parent',
    'post_type' => 'all',
]);
```

Here, the `fields` argument is set to `'id=>parent'`, which means only the 'id' and 'parent' fields will be retrieved.

### Retrieve Only IDs

To retrieve only the IDs of the posts, you can use the following code:

```php
PostQuery::select('ids')->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'fields' => 'ids',
    'post_type' => 'all',
]);
```

In this case, the `fields` argument is set to `'ids'`, indicating that only the post IDs will be returned.

### Find specific post id

The `find($postID)` method is used to create a WP_Query instance that retrieves a specific post by its ID. This method simplifies the process of querying a single post and allows you to specify the fields you want to retrieve.

To use the `find()` method, follow these steps:

1. Call the `find($postID)` method on an instance of the `PostQuery` class, passing the desired post ID as a parameter.
2. Chain other methods to further customize the query, such as `fields()`, if needed.
3. Finally, call the `get()` method to generate a WP_Query object with the specified arguments.

```php
PostQuery::find(1) // you can also pass an array of post ids.
    ->fields('ids')
    ->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'p' => 1,           // Retrieve the post with ID 1
    'fields' => 'ids',  // Retrieve only the post ID
    'post_type' => 'all',
]);
```

In this example, the `find(1)` method specifies that you want to retrieve the post with ID 1, the `fields('ids')` method indicates that you only want to retrieve the post ID, and the `post_type` argument is set to `'all'`, including all post types in the query.

## Author

The `PostQuery` class provides several methods for specifying the author(s) of the posts you want to retrieve. These methods allow you to filter posts based on author IDs, author usernames, and author IDs to include or exclude.

### author($authorID)

The `author($authorID)` method allows you to query posts authored by a specific user using their user ID. Here's an example:

```php
PostQuery::select()
    ->author(1)
    ->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'author' => 1,       // Retrieve posts authored by the user with ID 1
    'post_type' => 'all' // Include all post types in the query
]);
```

### authorName($authorUsername)

The `authorName($authorUsername)` method allows you to query posts authored by a specific user using their username. Here's an example:

```php
PostQuery::select()
    ->authorName('taylor')
    ->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'author_name' => 'taylor', // Retrieve posts authored by the user with the username 'taylor'
    'post_type' => 'all'      // Include all post types in the query
]);
```

### authorIn($authorIDs)

The `authorIn($authorIDs)` method allows you to query posts authored by one or more users specified by their user IDs. You can pass an array of user IDs to this method. Here's an example:

```php
PostQuery::select()
    ->authorIn([1, 2, 3])
    ->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'author__in' => [1, 2, 3], // Retrieve posts authored by users with IDs 1, 2, or 3
    'post_type' => 'all'       // Include all post types in the query
]);
```

### authorNotIn($authorIDs)

The `authorNotIn($authorIDs)` method allows you to exclude posts authored by one or more users specified by their user IDs. You can pass an array of user IDs to this method. Here's an example:

```php
PostQuery::select()
    ->authorNotIn([1, 2, 3])
    ->get();
```

This will generate the following WP_Query arguments:

```php
new WP_Query([
    'author__not_in' => [1, 2, 3], // Exclude posts authored by users with IDs 1, 2, or 3
    'post_type' => 'all'           // Include all post types in the query
]);
```

## Category

The `PostQuery` class allows you to query posts based on categories using various methods. These methods enable you to filter posts by category ID, category name, and include or exclude categories.

### cat($categoryID)

The `cat($categoryID)` method allows you to query posts belonging to a specific category by specifying its category ID. Here's an example:

```php
PostQuery::select()
    ->cat(1)
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'cat' => 1,       // Retrieve posts from category with ID 1
    'post_type' => 'all', // Include all post types in the query
]);
```

### categoryName($categoryName)

The `categoryName($categoryName)` method allows you to query posts belonging to a specific category by specifying its name. Here's an example:

```php
PostQuery::select()
    ->categoryName('sales')
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'category_name' => 'sales', // Retrieve posts from the 'sales' category
    'post_type' => 'all',       // Include all post types in the query
]);
```

### categoryIn($categoryIDs)

The `categoryIn($categoryIDs)` method allows you to include posts from one or more categories specified by their category IDs. You can pass an array of category IDs to this method. Here's an example:

```php
PostQuery::select()
    ->categoryIn([1, 2, 3])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'category__in' => [1, 2, 3], // Include posts from categories with IDs 1, 2, or 3
    'post_type' => 'all',        // Include all post types in the query
]);
```

### categoryNotIn($categoryIDs)

The `categoryNotIn($categoryIDs)` method allows you to exclude posts from one or more categories specified by their category IDs. You can pass an array of category IDs to this method. Here's an example:

```php
PostQuery::select()
    ->categoryNotIn([1, 2, 3])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'category__not_in' => [1, 2, 3], // Exclude posts from categories with IDs 1, 2, or 3
    'post_type' => 'all',           // Include all post types in the query
]);
```

## Tag

The `PostQuery` class allows you to query posts based on tags using various methods. These methods enable you to filter posts by tag name, tag ID, tag slugs, and include or exclude tags.

### tag($tagName)

The `tag($tagName)` method allows you to query posts associated with a specific tag by specifying its name. Here's an example:

```php
PostQuery::select()
    ->tag('programming')
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag' => 'programming', // Retrieve posts associated with the 'programming' tag
    'post_type' => 'all',   // Include all post types in the query
]);
```

### tagId($tagID)

The `tagId($tagID)` method allows you to query posts associated with a specific tag by specifying its ID. Here's an example:

```php
PostQuery::select()
    ->tagId(1)
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag_id' => 1,       // Retrieve posts associated with the tag with ID 1
    'post_type' => 'all', // Include all post types in the query
]);
```

### tagAnd($tagIDs)

The `tagAnd($tagIDs)` method allows you to query posts that are associated with all of the specified tags by specifying their IDs. You can pass an array of tag IDs to this method. Here's an example:

```php
PostQuery::select()
    ->tagAnd([1, 2])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag__and' => [1, 2], // Retrieve posts associated with both tags with IDs 1 and 2
    'post_type' => 'all', // Include all post types in the query
]);
```

### tagIn($tagIDs)

The `tagIn($tagIDs)` method allows you to query posts associated with one or more tags by specifying their IDs. You can pass an array of tag IDs to this method. Here's an example:

```php
PostQuery::select()
    ->tagIn([3, 4])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag__in' => [3, 4], // Retrieve posts associated with tags with IDs 3 or 4
    'post_type' => 'all', // Include all post types in the query
]);
```

### tagNotIn($tagIDs)

The `tagNotIn($tagIDs)` method allows you to exclude posts associated with one or more tags by specifying their IDs. You can pass an array of tag IDs to this method. Here's an example:

```php
PostQuery::select()
    ->tagNotIn([5, 6])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag__not_in' => [5, 6], // Exclude posts associated with tags with IDs 5 or 6
    'post_type' => 'all',    // Include all post types in the query
]);
```

### tagSlugAnd($tagSlugs)

The `tagSlugAnd($tagSlugs)` method allows you to query posts that are associated with all of the specified tags by specifying their slugs. You can pass an array of tag slugs to this method. Here's an example:

```php
PostQuery::select()
    ->tagSlugAnd(['dev', 'qa'])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag_slug__and' => ['dev', 'qa'], // Retrieve posts associated with both 'dev' and 'qa' tags
    'post_type' => 'all',            // Include all post types in the query
]);
```

### tagSlugIn($tagSlugs)

The `tagSlugIn($tagSlugs)` method allows you to query posts associated with one or more tags by specifying their slugs. You can pass an array of tag slugs to this method. Here's an example:

```php
PostQuery::select()
    ->tagSlugIn(['frontend', 'backend'])
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'tag_slug__in' => ['frontend', 'backend'], // Retrieve posts associated with 'frontend' or 'backend' tags
    'post_type' => 'all',                     // Include all post types in the query
]);
```

## Tax_Query

The `PostQuery` class allows you to filter posts based on taxonomy terms using the `taxQuery()` method. This method provides a powerful way to construct complex queries involving taxonomy terms and their relationships.

### Simple tax query

In its simplest form, you can use the `taxQuery()` method to filter posts by a single taxonomy and a list of terms. For example:

```php
PostQuery::select()
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query
                ->taxonomy('category')
                ->contains(['tee-shirt', 'sportswear'])
                ->searchByTermSlug()
        );
    })
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'post_type' => 'all',
    'tax_query' => [
        'relation' => 'AND',
        [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => ['tee-shirt', 'sportswear'],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]);
```

### 'OR' Relation

You can also use the 'OR' relation to query posts that match any of the specified taxonomy conditions. For example:

```php
PostQuery::select()
    ->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query
                ->taxonomy('category')
                ->contains([10, 20])
                ->searchById()
        )->orWhere(
            $query
                ->taxonomy('event')
                ->contains(['black-friday', 'christmas-sales'])
                ->searchByTermSlug()
        );
    })
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'post_type' => 'all',
    'tax_query' => [
        'relation' => 'OR',
        [
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => [10, 20],
            'operator' => 'IN',
            'include_children' => true,
        ],
        [
            'taxonomy' => 'event',
            'field' => 'slug',
            'terms' => ['black-friday', 'christmas-sales'],
            'operator' => 'IN',
            'include_children' => true,
        ],
    ],
]);
```

### Nested Conditions

You can also create nested conditions within the Tax_Query. In the following example, we use nested conditions with 'OR' and 'AND' relations:

```php
PostQuery::select()
    ->taxQuery(function (TaxQuery $query) {
        $query
            ->where(
                $query->taxonomy('status')->notContains(['private'])
            )
            ->orWhere(function (TaxQuery $query) {
                $query
                    ->where(
                        $query->taxonomy('attributes')->exists()
                    )
                    ->orWhere(
                        $query->taxonomy('product_cat')->contains(['tee-shirt', 'sportswear'])
                    );
            })
            ->orWhere(function (TaxQuery $query) {
                $query
                    ->where(
                        $query->taxonomy('promo_cat')->contains(['summer', 'blackfriday'])
                    )
                    ->andWhere(
                        $query->taxonomy('new_products')->notExists()
                    );
            });
    })
    ->get();
```

This generates the following WP_Query arguments:

```php
new WP_Query([
    'post_type' => 'all',
    'tax_query' => [
        'relation' => 'OR',
        [
            'taxonomy' => 'status',
            'field' => 'term_id',
            'terms' => [
                'private',
            ],
            'operator' => 'NOT IN',
            'include_children' => true,
        ],
        [
            'relation' => 'OR',
            [
                'taxonomy' => 'attributes',
                'field' => 'term_id',
                'terms' => null,
                'operator' => 'EXISTS',
                'include_children' => true,
            ],
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => [
                    'tee-shirt',
                    'sportswear',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
        ],
        [
            'relation' => 'AND',
            [
                'taxonomy' => 'promo_cat',
                'field' => 'term_id',
                'terms' => [
                    'summer',
                    'blackfriday',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
            [
                'taxonomy' => 'new_products',
                'field' => 'term_id',
                'terms' => null,
                'operator' => 'NOT EXISTS',
                'include_children' => true,
            ],
        ],
    ],
])
```

## Search

The `PostQuery` class allows you to perform keyword-based searches on posts using the `search()` method. This method helps you filter posts that match a specific keyword or phrase.

### search($keyword)

The `search($keyword)` method allows you to perform a keyword-based search for posts. You can specify the desired keyword or phrase as a parameter. Here's an example:

```php
PostQuery::select()
    ->search('my keyword')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    's' => 'my keyword', // Perform a keyword-based search for posts containing 'my keyword'
    'post_type' => 'all',
]);
```

## Post

The `PostQuery` class provides methods for filtering posts based on various post-related parameters. These methods allow you to specify the post type, post ID, post slug, post parent, and more.

### postType($type)

The `postType($type)` method allows you to specify the post type you want to query. You can provide the post type as a parameter. Here's an example:

```php
PostQuery::select()
    ->postType('article')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_type' => 'article', // Retrieve posts of the 'article' post type
]);
```

### postId($id)

The `postId($id)` method allows you to query a specific post by its ID. You can provide the post ID as a parameter. For example:

```php
PostQuery::select()
    ->postId(42)
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'p' => 42, // Retrieve the post with ID 42
    'post_type' => 'all'
]);
```

### postSlug($slug)

The `postSlug($slug)` method allows you to query a specific post by its slug. You can provide the post slug as a parameter. For example:

```php
PostQuery::select()
    ->postSlug('mon-article')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'name' => 'mon-article', // Retrieve the post with the slug 'mon-article'
    'post_type' => 'all'
]);

```

### postParent($parentID)

The `postParent($parentID)` method allows you to query posts that have a specific parent post. You can provide the parent post ID as a parameter. For example:

```php
PostQuery::select()
    ->postParent(5)
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_parent' => 5, // Retrieve posts with a parent post of ID 5
    'post_type' => 'all',
]);
```

### whereParentIn($parentIDs)

The `whereParentIn($parentIDs)` method allows you to query posts that have parent posts specified by their IDs. You can pass an array of parent post IDs to this method. For example:

```php
PostQuery::select()
    ->whereParentIn([1, 2, 3])
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_parent__in' => [1, 2, 3], // Retrieve posts with parent post IDs in the array
    'post_type' => 'all'
]);
```

### whereParentNotIn($parentIDs)

The `whereParentNotIn($parentIDs)` method allows you to exclude posts that have parent posts specified by their IDs. You can pass an array of parent post IDs to this method. For example:

```php
PostQuery::select()
    ->whereParentNotIn([4, 5, 6])
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_parent__not_in' => [4, 5, 6], // Exclude posts with parent post IDs in the array
    'post_type' => 'all'
]);
```

### whereIn($postIDs)

The `whereIn($postIDs)` method allows you to query posts with specific IDs. You can pass an array of post IDs to this method. For example:

```php
PostQuery::select()
    ->whereIn([7, 8, 9])
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post__in' => [7, 8, 9], // Retrieve posts with IDs in the array
    'post_type' => 'all'
]);
```

### whereNotIn($postIDs)

The `whereNotIn($postIDs)` method allows you to exclude posts with specific IDs. You can pass an array of post IDs to this method. For example:

```php
PostQuery::select()
    ->whereNotIn([10, 11, 12])
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post__not_in' => [10, 11, 12], // Exclude posts with IDs in the array
    'post_type' => 'all'
]);
```

### slugIn($slugs)

The `slugIn($slugs)` method allows you to query posts with specific slugs. You can pass an array of post slugs to this method. For example:

```php
PostQuery::select()
    ->slugIn(['slug-1', 'slug-2'])
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_name__in' => ['slug-1', 'slug-2'], // Retrieve posts with slugs in the array
    'post_type' => 'all'
]);
```

### Note: 'pagename' and 'page_id'

Unlike WP_Query, the `PostQuery` class does not provide separate methods for 'pagename' and 'page_id' parameters. Instead, you can use 'name' and 'p' parameters while specifying the 'post_type' as 'page' to achieve the equivalent results:

```php
PostQuery::select()
    ->postType('page')
    ->postSlug('my-page')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_type' => 'page',   // Query for pages
    'name' => 'my-page',     // Specify the page slug
    'post_type' => 'all'
]);
```

## Password

The `PostQuery` class provides methods to filter posts based on their password protection status and the specific post password.

### withPassword()

The `withPassword()` method allows you to query posts that have a password protection set. You can use this method to retrieve posts that require a password to access. For example:

```php
PostQuery::select()
    ->withPassword()
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'has_password' => true, // Retrieve posts with password protection
    'post_type' => 'all'
]);
```

### withoutPassword()

The `withoutPassword()` method allows you to query posts that do not have a password protection set. You can use this method to exclude posts that require a password to access. For example:

```php
PostQuery::select()
    ->withoutPassword()
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'has_password' => false, // Exclude posts with password protection
    'post_type' => 'all'
]);
```

### withPassword($password)

The `withPassword($password)` method allows you to query posts that have a specific post password set. You can provide the desired post password as a parameter. For example:

```php
PostQuery::select()
    ->withPassword('zxcvbn')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_password' => 'zxcvbn', // Retrieve posts with the specified post password
    'post_type' => 'all'
]);
```

## Status

The `PostQuery` class allows you to filter posts based on their status using the `postStatus()` method. This method helps you query posts with a specific status.

### postStatus($status)

The `postStatus($status)` method allows you to specify the status of posts you want to retrieve. You can provide the desired status as a parameter. For example:

```php
PostQuery::select()
    ->postStatus('publish')
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'post_status' => 'publish', // Retrieve posts with the 'publish' status
    'post_type' => 'all'
]);
```

You can use this method to query posts with different statuses such as 'publish,' 'draft,' 'pending,' or custom statuses defined in your WordPress installation. It provides precise control over the status of the posts you retrieve in your queries.

## Comment Parameter

The `PostQuery` class provides a method to filter posts based on the number of comments they have using the `commentCount()` method. This method allows you to retrieve posts with a specific number of comments.

### commentCount($count)

The `commentCount($count)` method allows you to specify the number of comments a post should have in order to be retrieved in the query. You can provide the desired comment count as a parameter. For example:

```php
PostQuery::select()
    ->commentCount(1)
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'comment_count' => 1, // Retrieve posts with exactly 1 comment
]);
```

## Pagination

The `PostQuery` class provides methods to control the pagination of query results. These methods allow you to specify the number of posts to retrieve per page, skip a certain number of posts, and more.

### take($count), limit($count) or postsPerPage($count)

The `take($count)`, `limit($count)`, and `postsPerPage($count)` methods allow you to specify the number of posts to retrieve per page. You can provide the desired post count as a parameter. For example:

```php
PostQuery::select()
    ->take(5)
    ->get();
```

These methods generate the following WP_Query argument:

```php
new WP_Query([
    'posts_per_page' => 5, // Retrieve 5 posts per page
]);
```

You can use these methods to control the number of posts displayed on each page of your query results.

### skip($count) or offset($count)

The `skip($count)` and `offset($count)` methods allow you to skip a certain number of posts in the query results. You can provide the desired skip count as a parameter. For example:

```php
PostQuery::select()
    ->skip(5)
    ->get();
```

These methods generate the following WP_Query argument:

```php
new WP_Query([
    'offset' => 5, // Skip the first 5 posts in the query results
]);
```

You can use these methods to skip a specific number of posts, useful for creating paginated queries.

### noPaging()

The `noPaging()` method allows you to disable pagination entirely, retrieving all posts matching the query without any pagination. For example:

```php
PostQuery::select()
    ->noPaging()
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'nopaging' => true, // Retrieve all posts without pagination
]);
```

Use this method when you want to retrieve all matching posts in a single query, regardless of the number.

### postsPerArchivePage($count)

The `postsPerArchivePage($count)` method allows you to specify the number of posts to display per archive page. This is particularly useful for archive pages like category or tag archives. For example:

```php
PostQuery::select()
    ->postsPerArchivePage(5)
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'posts_per_archive_page' => 5, // Display 5 posts per archive page
]);
```

You can use this method to control the number of posts displayed on archive pages.

### page($pageNumber)

The `page($pageNumber)` method allows you to specify the page number when paginating query results. You can provide the desired page number as a parameter. For example:

```php
PostQuery::select()
    ->page(666)
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'page' => 666, // Retrieve results for page number 666
]);
```

Use this method when you want to retrieve a specific page of results in a paginated query.

### ignoreStickyPosts()

The `ignoreStickyPosts()` method allows you to exclude sticky posts from the query results. Sticky posts are posts that are pinned to the top of the list. For example:

```php
PostQuery::select()
    ->ignoreStickyPosts()
    ->get();
```

This generates the following WP_Query argument:

```php
new WP_Query([
    'ignore_sticky_posts' => true, // Exclude sticky posts from the query
]);
```

## Order

The `PostQuery` class provides methods to control the order in which posts are retrieved. You can specify one or more orderby parameters to sort the query results based on various criteria.

### orderBy($field, $order = 'DESC')

The `orderBy($field, $order = 'DESC')` method allows you to specify the field by which the posts should be ordered and the order in which they should be sorted. You can provide the desired field and order as parameters. For example:

```php
PostQuery::select()
    ->orderBy('most_comments')
    ->orderBy('post_date', 'ASC')
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'orderby' => [
        'most_comments' => 'DESC', // Order by 'most_comments' in descending order
        'post_date' => 'ASC',      // Then order by 'post_date' in ascending order
    ],
]);
```

## Date

The `PostQuery` class allows you to query posts based on date-related criteria using the `DateQuery` class. This class provides methods to create complex date queries, allowing you to filter posts by their publication, modification, or custom dates.

### `dateQuery($callback)`

The `dateQuery($callback)` method allows you to define a complex date query using the `DateQuery` class. You can pass a callback function to create the date query conditions. For example:

```php
PostQuery::select()->dateQuery(function (DateQuery $query) {
    $query
        ->where(
            $query->date('edited_at')->before('2022-01-01')->after([
                'year' => '2021',
                'month' => '01',
                'day' => '01',
            ])
        )
        ->inclusive();
})->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'date_query' => [
        'relation' => 'AND',
        [
            'column' => 'edited_at',
            'before' => [
                'year' => 2022,
                'month' => 1,
                'day' => 1,
                'hour' => 12,
                'minute' => 0,
                'second' => 0,
            ],
            'after' => [
                'year' => 2021,
                'month' => 1,
                'day' => 1,
            ],
        ],
        'inclusive' => true,
    ],
]);
```

You can use this method to create complex date queries that include conditions like before, after, and inclusive/exclusive settings.

### `date($column = 'post_date')`

The `date($column = 'post_date')` method within the `DateQuery` class allows you to specify the column or type of date to query. You can choose from 'post_date,' 'post_modified,' or other custom date columns. For example:

```php
$query->date('edited_at');
```

### `before($date)`

The `before($date)` method within the `DateQuery` class allows you to specify that the date should be before a certain point in time. You can provide the date in various formats, such as 'YYYY-MM-DD' or an array specifying 'year,' 'month,' 'day,' 'hour,' 'minute,' and 'second.'

### `after($date)`

The `after($date)` method within the `DateQuery` class allows you to specify that the date should be after a certain point in time. Like the `before()` method, you can provide the date in various formats.

### `between($start, $end)`

The `between($start, $end)` method within the `DateQuery` class allows you to specify that the date should be between two points in time. You can provide the start and end dates in various formats.

### `created()`

The `modified()` method within the `DateQuery` class allows you to specify that the date query should apply to the last modified date of the post, as opposed to the default 'post_date' column. This is useful for distinguishing between post creation and modification dates.

### `inclusive()`

The `inclusive()` method within the `DateQuery` class allows you to specify that the date query should include posts that match the exact date and time specified. This is particularly useful when using the `before()` and `after()` methods.

## Meta Query

The `PostQuery` class allows you to perform custom queries based on post metadata using the `MetaQuery` class. This class provides methods to create complex meta queries, allowing you to filter posts based on custom field values.

### `metaQuery($callback)`

The `metaQuery($callback)` method allows you to define a complex meta query using the `MetaQuery` class. You can pass a callback function to create the meta query conditions. For example:

```php
PostQuery::select()->metaQuery(function (MetaQuery $query) {
    $query->where(
        $query->meta('status')->equalTo('active')
    );
})->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'status',
            'compare' => '=',
            'type' => 'CHAR',
            'value' => 'active',
        ],
    ],
]);
```

### meta($key)

The `meta($key)` method within the `MetaQuery` class allows you to specify the custom field key you want to query. For example:

### Comparison Methods

The `MetaQuery` class offers several methods to specify the comparison operations to perform on custom fields. You can chain these methods to build complex conditions.

#### ofType($type)

This method allows you to specify the type of comparison to use. Possible types are class constants such as `MetaQueryBuilder::NUMERIC`, `MetaQueryBuilder::CHAR`, `MetaQueryBuilder::DATE`, etc. By default, the type is automatically determined based on the value provided during comparison.

Example of usage:

```php
$query->meta('my_post_meta')->ofType('numeric'); // You can also use a number
```

##### Note :

The `detectValueType` method is used internally to automatically determine the data type of the value provided during comparison. It is called automatically when you use other comparison methods.

This method performs the following checks to determine the data type:

- If the value is an array, it takes the data type of the first element.
- If a data type is explicitly set using the `ofType` method, it uses that data type.
- If the value is `null`, it defaults to the `CHAR` data type.
- If the value is `0` or `1`, it uses the `BINARY` data type.
- If the value is a negative integer, it uses the `SIGNED` data type.
- If the value is a non-negative integer, it uses the `UNSIGNED` data type.
- If the value is a floating-point number, it uses the `DECIMAL` data type.
- If the value is a string containing only a numeric date (e.g., '2022-01-01'), it uses the `DATE` data type.
- If the value is a string containing only a numeric time (e.g., '12:00:00'), it uses the `TIME` data type.
- If the value is a string containing both date and time (e.g., '2022-01-01 12:00:00'), it uses the `DATETIME` data type.
- If none of the above conditions apply, it defaults to the `CHAR` data type.

#### equalTo($value)

This method specifies that the custom field must be equal to a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->equalTo('active'); // You can also use a number
```

#### notEqualTo(mixed $value)

This method specifies that the custom field must not be equal to a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->notEqualTo('inactive');
```

#### greaterThan($value)

This method specifies that the custom field must be greater than a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->greaterThan(100);
```

#### greaterOrEqualTo($value)

This method specifies that the custom field must be greater than or equal to a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->greaterOrEqualTo(100);
```

#### lessThan($value)

This method specifies that the custom field must be less than a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->lessThan(5);
```

#### lessOrEqualTo($value)

This method specifies that the custom field must be less than or equal to a certain value.

Example of usage:

```php
$query->meta('my_post_meta')->lessOrEqualTo(5);
```

#### `between(mixed $lowerBoundary, mixed $upperBoundary): self`

This method specifies that the custom field must be between two given values.

Example of usage:

```php
$query->meta('my_post_meta')->between('2022-01-01', '2022-12-31');
```

#### notBetween($lowerBoundary, $upperBoundary)

This method specifies that the custom field must not be between two given values.

Example of usage:

```php
$query->meta('my_post_meta')->notBetween('2022-01-01', '2022-12-31');
```

#### `like(string $value): self`

This method specifies a partial match of the custom field with a string.

Example of usage:

```php
$query->meta('my_post_meta')->like('keyword');
```

#### notLike($value)

This method specifies that the custom field must not partially match a string.

Example of usage:

```php
$query->meta('my_post_meta')->notLike('excluded');
```

#### in($values)

This method specifies that the custom field must match one of the values in a given array.

Example of usage:

```php
$query->meta('my_post_meta')->in(['value1', 'value2', 'value3']);
```

#### notIn($values)

This method specifies that the custom field must not match any of the values in a given array.

Example of usage:

```php
$query->meta('my_post_meta')->notIn(['excluded1', 'excluded2']);
```

#### state($value)

The `state($value)` method within the `MetaQuery` class allows you to use the named meta queries and use this state for ordering. See [this section of the documentation](https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters) for more information.

Example of usage:

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query
                ->meta('menu_order')
                    ->state('menu_order_state')
                    ->greaterThan(10)
            );
    
    })
    ->orderBy('menu_order_state', 'ASC')
    ->get();
```

#### exists()

This method specifies that the custom field must exist (be defined).

Example of usage:

```php
$query->meta('my_post_meta')->exists();
```

#### notExists()

This method specifies that the custom field must not exist (not be defined).

Example of usage:

```php
$query->meta('my_post_meta')->notExists();
```

### `metaQuery` Method Chaining

You can chain multiple `meta()` methods to create complex meta queries. For example:

```php
PostQuery::select()->metaQuery(function (MetaQuery $query) {
    $query
        ->where(
            $query->meta('status')->equalTo('active')
        )
        ->orWhere(
            $query->meta('highlighted')->equalTo(1)
        );

})->get();
```

This generates a meta query with an 'OR' relation between the two conditions.

### Nested Meta Queries

You can create nested meta queries by using the `MetaQuery` class within the `metaQuery()` callback. This allows you to create complex meta queries with multiple levels of conditions. For example:

```php
PostQuery::select()
    ->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query->meta('status')->equalTo('active')
            )
            ->orWhere(function (MetaQuery $query) {
                $query
                    ->where(
                        $query->meta('_sku')->equalTo('H123456789')
                    )
                    ->orWhere(
                        $query->meta('_sku')->equalTo('D123456784')
                    );
            })
            ->orWhere(function (MetaQuery $query) {
                $query
                    ->where(
                        $query->meta('promo_cat')->notIn(['summer', 'blackfriday'])->state('promo_cat')
                    )
                    ->andWhere(
                        $query->meta('promo_cat')->notEqualTo('sales')
                    );
            })
            ->orWhere(
                $query->meta('sale_date')
                    ->between('2024-01-01', '2024-12-31')
            );
    
    })
    ->orderBy('promo_cat')
    ->get();
```

## Permission

The `PostQuery` class allows you to query posts based on specific user permissions. You can use the following permission parameters to filter posts based on their accessibility to users.

### `userPermission(string $permission)`

The `userPermission` method is used to filter posts based on user permissions. It accepts one parameter:

- `$permission` (string): Specifies the type of permission to filter posts by. Valid values include:

    - `'readable'`: This permission filters posts that the user can read.
    - `'editable'`: This permission filters posts that the user can edit.

#### Filter posts that the user can read.

```php
PostQuery::select()
    ->userPermission('readable')
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'perm' => 'readable',
    'post_type' => 'all',
]);
```

#### Filter posts that the user can edit.

```php
PostQuery::select()
    ->userPermission('editable')
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'perm' => 'editable',
    'post_type' => 'all',
]);
```

#### Invalid permissions

```php
PostQuery::select()
    ->userPermission('invalid')
    ->get();
```

When an invalid permission is provided, all posts are returned in the generated WP_Query:

```php
new WP_Query([
    'post_type' => 'all',
]);
```

## Mimetype

The `PostQuery` class allows you to filter posts based on their MIME types. You can use the `postMimeType` method to specify the MIME type(s) you want to filter by.

### `postMimeType(string|array $mimeTypes)`

The `postMimeType` method filters posts based on MIME type(s). It accepts one parameter:

- `$mimeTypes` (string|array): Specifies the MIME type(s) to filter posts by. You can provide a single MIME type as a string or an array of multiple MIME types.

#### Filter posts by a single MIME type (e.g., 'image/gif').

```php
PostQuery::select()
    ->postMimeType('image/gif')
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'post_mime_type' => 'image/gif',
    'post_type' => 'all',
]);
```

#### Filter posts by an array of MIME types.

```php
PostQuery::select()
    ->postMimeType(['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon'])
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'post_mime_type' => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon'],
    'post_type' => 'all',
]);
```

## Cache

The `PostQuery` class provides methods for controlling caching behavior when querying posts.

### cacheResults()

The `cacheResults` method enables caching of query results. When you use this method, the query results will be cached for faster retrieval. It doesn't accept any parameters.

```php
PostQuery::select()
    ->cacheResults()
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'cache_results' => true,
    'post_type' => 'all',
]);
```

### updateMetaCache($update)

The `updateMetaCache` method allows you to control whether post meta data is cached. You can specify whether to update the post meta cache or not using this method.

- `$update` (bool): Pass `true` to update the post meta cache or `false` to disable it.

```php
PostQuery::select()
    ->updateMetaCache(false)
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'update_post_meta_cache' => false,
    'post_type' => 'all',
]);
```

### updateTermCache($update)

The `updateTermCache` method allows you to control whether post term data is cached. You can specify whether to update the post term cache or not using this method.

- `$update` (bool): Pass `true` to update the post term cache or `false` to disable it.

```php
PostQuery::select()
    ->updateTermCache(true)
    ->get();
```

This method generates the following WP_Query argument:

```php
new WP_Query([
    'update_post_term_cache' => true,
    'post_type' => 'all',
]);
```
