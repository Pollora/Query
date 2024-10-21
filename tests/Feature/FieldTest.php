<?php

use Pollora\Query\PostQuery;

test('Field: ids', function () {
    $args = PostQuery::find(1)
        ->fields('ids')
        ->getArguments();

    expect($args)->toMatchArray([
        'p' => 1,
        'fields' => 'ids',
    ]);
});

test('Field: wildcard', function () {
    $args = PostQuery::find(1)
        ->fields('*')
        ->getArguments();

    expect($args)->toMatchArray([
        'p' => 1,
        'fields' => 'all',
    ]);
});
