<?php

use Pollora\Query\PostQuery;
use Pollora\Query\QueryException;

test('Order: orderby', function () {
    $args = PostQuery::select()
        ->orderBy('most_comments')
        ->orderBy('post_date', 'ASC')
        ->getArguments();

    expect($args)->toMatchArray([
        'orderby' => [
            'most_comments' => 'DESC',
            'post_date' => 'ASC',
        ],
    ]);
});

test('Order: orderby accepts a map of fields', function () {
    $args = PostQuery::select()
        ->orderBy(['post_date' => 'asc', 'title' => 'DESC'])
        ->getArguments();

    expect($args['orderby'])->toBe(['post_date' => 'ASC', 'title' => 'DESC']);
});

test('Order: inRandomOrder is an orderby array and can be replaced by real columns', function () {
    $random = PostQuery::select()->inRandomOrder()->getArguments();
    $replaced = PostQuery::select()->inRandomOrder()->latest()->getArguments();

    expect($random['orderby'])->toBe(['rand' => 'ASC'])
        ->and($replaced['orderby'])->toBe(['post_date' => 'DESC']);
});

test('Order: latest and oldest', function () {
    expect(PostQuery::select()->latest()->getArguments()['orderby'])->toBe(['post_date' => 'DESC'])
        ->and(PostQuery::select()->oldest()->getArguments()['orderby'])->toBe(['post_date' => 'ASC']);
});

test('Order: an invalid direction throws', function () {
    PostQuery::select()->orderBy('post_date', 'sideways');
})->throws(QueryException::class, 'Invalid order direction supplied: SIDEWAYS');

test('Order: an invalid direction in a map throws', function () {
    PostQuery::select()->orderBy(['post_date' => 'ASC', 'title' => 'up']);
})->throws(QueryException::class);
