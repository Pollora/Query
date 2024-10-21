<?php

use Pollora\Query\PostQuery;

it('Mimetype: string', function () {
    $args = PostQuery::select()
        ->postMimeType('image/gif')
        ->getArguments();

    expect($args)->toMatchArray([
        'post_mime_type' => 'image/gif',
    ]);
});

it('Mimetype: array', function () {
    $args = PostQuery::select()
        ->postMimeType(['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon'])
        ->getArguments();

    expect($args)->toMatchArray([
        'post_mime_type' => ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/tiff', 'image/x-icon'],
    ]);
});
