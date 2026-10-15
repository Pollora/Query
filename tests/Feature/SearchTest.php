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

test('Search: exact, sentence and search_columns', function () {
    $args = PostQuery::select()
        ->search('my keyword')->exactSearch()->searchSentence()->searchColumns(['post_title', 'post_excerpt'])
        ->getArguments();

    expect($args)->toMatchArray([
        's' => 'my keyword', 'exact' => true, 'sentence' => true, 'search_columns' => ['post_title', 'post_excerpt'],
    ]);
});
