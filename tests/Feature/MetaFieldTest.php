<?php

use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;

test('MetaField: top-level meta arguments', function () {
    $args = PostQuery::select()
        ->metaKey('color')->metaValue('red')->metaCompare('!=')->metaType('char')
        ->metaCompareKey('LIKE')->metaTypeKey('char')
        ->getArguments();

    expect($args)->toMatchArray([
        'meta_key' => 'color', 'meta_value' => 'red', 'meta_compare' => '!=', 'meta_type' => 'CHAR',
        'meta_compare_key' => 'LIKE', 'meta_type_key' => 'CHAR',
    ]);
});

test('MetaField: orderByMeta', function () {
    $text = PostQuery::select()->orderByMeta('subtitle', 'ASC')->getArguments();
    $numeric = PostQuery::select()->orderByMeta('price', 'DESC', true)->getArguments();

    expect($text)->toMatchArray(['meta_key' => 'subtitle', 'orderby' => ['meta_value' => 'ASC']])
        ->and($numeric)->toMatchArray(['meta_key' => 'price', 'orderby' => ['meta_value_num' => 'DESC']]);
});

test('MetaQuery: regexp operators', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('a')->regexp('^x'))
            ->where($q->meta('b')->notRegexp('y$'))
            ->where($q->meta('c')->rlike('z'));
    })->getArguments();

    expect(array_column(array_filter($args['meta_query'], 'is_array'), 'compare'))->toBe(['REGEXP', 'NOT REGEXP', 'RLIKE'])
        ->and($args['meta_query'][0]['value'])->toBe('^x');
});

test('MetaQuery: key comparison', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('attr_')->keyCompare('LIKE')->keyType('char')->exists());
    })->getArguments();

    expect($args['meta_query'][0])->toMatchArray(['key' => 'attr_', 'compare_key' => 'LIKE', 'type_key' => 'CHAR', 'compare' => 'EXISTS']);
});

test('MetaQuery: clauses without key comparison have no compare_key', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('a')->equalTo('x'));
    })->getArguments();

    expect($args['meta_query'][0])->not->toHaveKeys(['compare_key', 'type_key', 'state']);
});
