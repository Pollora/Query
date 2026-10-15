<?php

use Pollora\Query\PostQuery;

test('Caching: cache_results', function () {
    $args = PostQuery::select()
        ->cacheResults()
        ->getArguments();

    expect($args)->toMatchArray([
        'cache_results' => true,
    ]);
});

test('Caching: update_post_meta_cache=>false', function () {
    $args = PostQuery::select()
        ->updateMetaCache(false)
        ->getArguments();

    expect($args)->toMatchArray([
        'update_post_meta_cache' => false,
    ]);
});

test('Caching: update_post_term_cache=>true', function () {
    $args = PostQuery::select()
        ->updateTermCache(true)
        ->getArguments();

    expect($args)->toMatchArray([
        'update_post_term_cache' => true,
    ]);
});

test('Caching: lazy_load_term_meta, no_found_rows and suppress_filters', function () {
    $args = PostQuery::select()->lazyLoadTermMeta(false)->noFoundRows()->suppressFilters()->getArguments();

    expect($args)->toMatchArray(['lazy_load_term_meta' => false, 'no_found_rows' => true, 'suppress_filters' => true]);
});
