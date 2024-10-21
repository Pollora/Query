<?php

use Pollora\Query\PostQuery;

test('Permission: readable', function () {
    $args = PostQuery::select()
        ->userPermission('readable')
        ->getArguments();

    expect($args)->toMatchArray([
        'perm' => 'readable',
    ]);
});

test('Permission: editable', function () {
    $args = PostQuery::select()
        ->userPermission('editable')
        ->getArguments();

    expect($args)->toMatchArray([
        'perm' => 'editable',
    ]);
});

test('Permission: invalid', function () {
    $args = PostQuery::select()
        ->userPermission('invalid')
        ->getArguments();

    expect($args)->toMatchArray([
        'fields' => 'all',
    ]);
});
