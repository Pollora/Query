<?php

use Pollora\Query\PostQuery;

test('Status: post_status', function () {
    $args = PostQuery::select()
        ->postStatus('publish')
        ->getArguments();

    expect($args)->toMatchArray([
        'post_status' => 'publish',
    ]);
});
