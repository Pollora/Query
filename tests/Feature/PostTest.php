<?php

use Pollora\Query\PostQuery;

test('Post: post_type', function () {
    $args = PostQuery::select()
        ->postType('article')
        ->getArguments();

    expect($args)->toMatchArray([
        'post_type' => 'article',
    ]);
});

test('Post: p (post_id)', function () {
    $args = PostQuery::select()
        ->postId(42)
        ->getArguments();

    expect($args)->toMatchArray([
        'p' => 42,
    ]);
});

test('Post: name (post_slug)', function () {
    $args = PostQuery::select()
        ->postSlug('mon-article')
        ->getArguments();

    expect($args)->toMatchArray([
        'name' => 'mon-article',
    ]);
});

test('Post: post_parent', function () {
    $args = PostQuery::select()
        ->postParent(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'post_parent' => 5,
    ]);
});

test('Post: post_parent__in', function () {
    $args = PostQuery::select()
        ->whereParentIn([1, 2, 3])
        ->getArguments();

    expect($args)->toMatchArray([
        'post_parent__in' => [1, 2, 3],
    ]);
});

test('Post: post_parent__not_in', function () {
    $args = PostQuery::select()
        ->whereParentNotIn([4, 5, 6])
        ->getArguments();

    expect($args)->toMatchArray([
        'post_parent__not_in' => [4, 5, 6],
    ]);
});

test('Post: post__in', function () {
    $args = PostQuery::select()
        ->whereIn([7, 8, 9])
        ->getArguments();

    expect($args)->toMatchArray([
        'post__in' => [7, 8, 9],
    ]);
});

test('Post: post__not_in', function () {
    $args = PostQuery::select()
        ->whereNotIn([10, 11, 12])
        ->getArguments();

    expect($args)->toMatchArray([
        'post__not_in' => [10, 11, 12],
    ]);
});

test('Post: post_name__in', function () {
    $args = PostQuery::select()
        ->slugIn(['slug-1', 'slug-2'])
        ->getArguments();

    expect($args)->toMatchArray([
        'post_name__in' => ['slug-1', 'slug-2'],
    ]);
});
