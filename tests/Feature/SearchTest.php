<?php

use Pollora\Query\PostQuery;

test('Search: s (keyword)', function () {
    $args = PostQuery::select()
        ->search('my keyword')
        ->getArguments();

    expect($args)->toMatchArray([
        's' => 'my keyword',
    ]);
});
