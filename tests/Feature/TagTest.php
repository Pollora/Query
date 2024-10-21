<?php

use Pollora\Query\PostQuery;

test('Tag: tag', function () {
    $args = PostQuery::select()
        ->tag('programming')
        ->getArguments();

    expect($args)->toMatchArray([
        'tag' => 'programming',
    ]);
});

test('Tag: tag_id', function () {
    $args = PostQuery::select()
        ->tagId(1)
        ->getArguments();

    expect($args)->toMatchArray([
        'tag_id' => 1,
    ]);
});

test('Tag: tag__and', function () {
    $args = PostQuery::select()
        ->tagAnd([1, 2])
        ->getArguments();

    expect($args)->toMatchArray([
        'tag__and' => [1, 2],
    ]);
});

test('Tag: tag__in', function () {
    $args = PostQuery::select()
        ->tagIn([3, 4])
        ->getArguments();

    expect($args)->toMatchArray([
        'tag__in' => [3, 4],
    ]);
});

test('Tag: tag__not_in', function () {
    $args = PostQuery::select()
        ->tagNotIn([5, 6])
        ->getArguments();

    expect($args)->toMatchArray([
        'tag__not_in' => [5, 6],
    ]);
});

test('Tag: tag_slug__and', function () {
    $args = PostQuery::select()
        ->tagSlugAnd(['dev', 'qa'])
        ->getArguments();

    expect($args)->toMatchArray([
        'tag_slug__and' => ['dev', 'qa'],
    ]);
});

test('Tag: tag_slug__in', function () {
    $args = PostQuery::select()
        ->tagSlugIn(['frontend', 'backend'])
        ->getArguments();

    expect($args)->toMatchArray([
        'tag_slug__in' => ['frontend', 'backend'],
    ]);
});
