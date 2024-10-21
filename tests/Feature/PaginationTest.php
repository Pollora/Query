<?php

use Pollora\Query\PostQuery;

test('pagination: take', function () {
    $args = PostQuery::select()
        ->take(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'posts_per_page' => 5,
    ]);
});

test('pagination: limit', function () {
    $args = PostQuery::select()
        ->limit(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'posts_per_page' => 5,
    ]);
});

test('pagination: post_per_page', function () {
    $args = PostQuery::select()
        ->postsPerPage(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'posts_per_page' => 5,
    ]);
});

test('pagination: skip', function () {
    $args = PostQuery::select()
        ->skip(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'offset' => 5,
    ]);
});

test('pagination: offset', function () {
    $args = PostQuery::select()
        ->offset(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'offset' => 5,
    ]);
});

test('pagination: nopaging', function () {
    $args = PostQuery::select()
        ->noPaging()
        ->getArguments();

    expect($args)->toMatchArray([
        'nopaging' => true,
    ]);
});

test('pagination: posts_per_archive_page', function () {
    $args = PostQuery::select()
        ->postsPerArchivePage(5)
        ->getArguments();

    expect($args)->toMatchArray([
        'posts_per_archive_page' => 5,
    ]);
});

test('pagination: page', function () {
    $args = PostQuery::select()
        ->page(666)
        ->getArguments();

    expect($args)->toMatchArray([
        'page' => 666,
    ]);
});

test('pagination: ignore_sticky_posts', function () {
    $args = PostQuery::select()
        ->ignoreStickyPosts()
        ->getArguments();

    expect($args)->toMatchArray([
        'ignore_sticky_posts' => true,
    ]);
});
