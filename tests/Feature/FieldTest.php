<?php

use Pollora\Query\PostQuery;
use Pollora\Query\QueryException;

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

test('Field: id=>parent', function () {
    expect(PostQuery::select('id=>parent')->getArguments()['fields'])->toBe('id=>parent');
});

test('Field: invalid', function () {
    PostQuery::select('bogus');
})->throws(QueryException::class, 'Invalid fields value supplied: bogus');
