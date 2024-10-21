<?php

use Pollora\Query\PostQuery;

test('Comment: comment_count', function () {
    $args = PostQuery::select()
        ->commentCount(1)
        ->getArguments();

    expect($args)->toMatchArray([
        'comment_count' => 1,
    ]);
});
