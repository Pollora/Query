<?php

use Pollora\Query\PostQuery;

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
