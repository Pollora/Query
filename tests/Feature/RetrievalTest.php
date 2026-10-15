<?php

use Pollora\Query\PostQuery;

beforeEach(function () {
    WP_Query::$log = [];
    WP_Query::$fakePosts = [];
    WP_Query::$fakeFound = 0;
});

test('Retrieval: first returns the first post and asks for a single row', function () {
    $first = new stdClass;
    WP_Query::$fakePosts = [$first, new stdClass];

    expect(PostQuery::select()->postType('page')->first())->toBe($first)
        ->and(WP_Query::$log[0])->toMatchArray(['post_type' => 'page', 'posts_per_page' => 1, 'no_found_rows' => true]);
});

test('Retrieval: first returns null when nothing matches', function () {
    expect(PostQuery::select()->first())->toBeNull();
});

test('Retrieval: first ignores paging that would skip the result', function () {
    PostQuery::select()->noPaging()->offset(5)->paged(3)->page(2)->first();

    expect(WP_Query::$log[0])->not->toHaveKeys(['nopaging', 'offset', 'paged', 'page']);
});

test('Retrieval: ids asks for ids and returns integers', function () {
    WP_Query::$fakePosts = ['3', 5];

    expect(PostQuery::select()->limit(20)->ids())->toBe([3, 5])
        ->and(WP_Query::$log[0])->toMatchArray(['fields' => 'ids', 'posts_per_page' => 20, 'no_found_rows' => true]);
});

test('Retrieval: count returns the total regardless of limit and page', function () {
    WP_Query::$fakeFound = 42;

    expect(PostQuery::select()->limit(10)->paged(4)->count())->toBe(42)
        ->and(WP_Query::$log[0])->toMatchArray(['fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => false, 'ignore_sticky_posts' => true])
        ->and(WP_Query::$log[0])->not->toHaveKey('paged');
});

test('Retrieval: count re-enables found rows even after noFoundRows', function () {
    PostQuery::select()->noFoundRows()->count();

    expect(WP_Query::$log[0]['no_found_rows'])->toBeFalse();
});

test('Retrieval: exists', function () {
    expect(PostQuery::select()->exists())->toBeFalse();

    WP_Query::$fakePosts = [7];

    expect(PostQuery::select()->exists())->toBeTrue()
        ->and(WP_Query::$log[1])->toMatchArray(['fields' => 'ids', 'posts_per_page' => 1, 'no_found_rows' => true]);
});

test('Retrieval: terminals do not alter the builder', function () {
    $query = PostQuery::select()->postType('page')->limit(10)->paged(2);
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before);
});

test('Retrieval: find(id)->first() targets that post', function () {
    $post = new stdClass;
    WP_Query::$fakePosts = [$post];

    expect(PostQuery::find(5)->first())->toBe($post)
        ->and(WP_Query::$log[0])->toMatchArray(['p' => 5, 'post_type' => 'any']);
});
