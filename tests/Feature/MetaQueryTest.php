<?php

use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\Query;

test('MetaQuery: meta_query (simple)', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query->where(
            Query::meta('status')->equalTo('active')
        );
    })->getArguments();

    expect($args)->toMatchArray([
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'status',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'active',
            ],
        ],
    ]);
});

test('MetaQuery: meta_query (or relation)', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query->meta('status')->equalTo('active')
            )
            ->orWhere(
                $query->meta('highlighted')->equalTo(1)
            );

    })->getArguments();

    expect($args)->toMatchArray([
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'status',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'active',
            ],
            [
                'key' => 'highlighted',
                'compare' => '=',
                'type' => 'BINARY',
                'value' => 1,
            ],
        ],
    ]);
});

test('MetaQuery: meta_query called twice', function () {
    $args = PostQuery::select()
        ->metaQuery(function (MetaQuery $query) {
            $query
                ->where(
                    $query->meta('status')->equalTo('active')
                )->orWhere(
                    $query->meta('highlighted')->equalTo(0)
                );
        })
        ->metaQuery(Query::meta('hide')->notEqualTo(1))
        ->getArguments();

    expect($args)->toMatchArray([
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'status',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'active',
            ],
            [
                'key' => 'highlighted',
                'compare' => '=',
                'type' => 'BINARY',
                'value' => 0,
            ],
            [
                'key' => 'hide',
                'compare' => '!=',
                'type' => 'BINARY',
                'value' => 1,
            ],
        ],
    ]);
});

test('MetaQuery: meta_query (and relation)', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query->meta('date')->greaterThan('2021-01-01')
            )
            ->andWhere(
                $query->meta('date')->lessOrEqualTo('2023-08-04 23:00:00')
            );

    })->getArguments();

    expect($args)->toMatchArray([
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'date',
                'compare' => '>',
                'type' => 'DATE',
                'value' => '2021-01-01',
            ],
            [
                'key' => 'date',
                'compare' => '<=',
                'type' => 'DATETIME',
                'value' => '2023-08-04 23:00:00',
            ],
        ],
    ]);
});

test('MetaQuery: meta_query (nested)', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query
            ->where(
                $query->meta('status')->equalTo('active')
            )
            ->orWhere(function (MetaQuery $query) {
                $query
                    ->where(
                        $query->meta('_sku')->equalTo('H123456789')
                    )
                    ->orWhere(
                        $query->meta('_sku')->equalTo('D123456784')
                    );
            })
            ->orWhere(function (MetaQuery $query) {
                $query
                    ->where(
                        $query->meta('promo_cat')->notIn(['summer', 'blackfriday'])->state('promo_cat')
                    )
                    ->andWhere(
                        $query->meta('promo_cat')->notEqualTo('sales')
                    );
            })
            ->orWhere(
                $query->meta('sale_date')
                    ->between('2024-01-01', '2024-12-31')
            );

    })->getArguments();

    expect($args)->toMatchArray([
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'status',
                'compare' => '=',
                'type' => 'CHAR',
                'value' => 'active',
            ],
            [
                'relation' => 'OR',
                [
                    'key' => '_sku',
                    'compare' => '=',
                    'type' => 'CHAR',
                    'value' => 'H123456789',
                ],
                [
                    'key' => '_sku',
                    'compare' => '=',
                    'type' => 'CHAR',
                    'value' => 'D123456784',
                ],
            ],
            [
                'relation' => 'AND',
                'promo_cat' => [
                    'key' => 'promo_cat',
                    'compare' => 'NOT IN',
                    'type' => 'CHAR',
                    'value' => [
                        'summer',
                        'blackfriday',
                    ],
                ],
                [
                    'key' => 'promo_cat',
                    'compare' => '!=',
                    'type' => 'CHAR',
                    'value' => 'sales',
                ],
            ],
            [
                'key' => 'sale_date',
                'compare' => 'BETWEEN',
                'type' => 'DATE',
                'value' => [
                    '2024-01-01',
                    '2024-12-31',
                ],
            ],
        ],
    ]);
});

test('MetaQuery: ofType overrides the detected type', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('price')->ofType('NUMERIC')->greaterThan(10));
    })->getArguments();

    expect($args['meta_query'][0])->toBe(['key' => 'price', 'compare' => '>', 'type' => 'NUMERIC', 'value' => 10]);
});

test('MetaQuery: ofType survives a later comparison', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('d')->ofType('DATE')->between('2024-01-01', '2024-02-01'));
        $query->where($query->meta('n')->greaterThan(1)->ofType('DECIMAL'));
    })->getArguments();

    expect($args['meta_query'][0]['type'])->toBe('DATE')
        ->and($args['meta_query'][1]['type'])->toBe('DECIMAL');
});

test('MetaQuery: between detects a type that fits both boundaries', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('a')->between(1, 50));
        $query->where($query->meta('b')->between(-5, 5));
        $query->where($query->meta('c')->between(1, 2.5));
        $query->where($query->meta('d')->between('2024-01-01', '2024-02-01'));
        $query->where($query->meta('e')->in(['x', 3]));
    })->getArguments();

    expect(array_column(array_slice($args['meta_query'], 1), 'type'))->toBe(['UNSIGNED', 'SIGNED', 'DECIMAL', 'DATE', 'CHAR']);
});

test('MetaQuery: an empty in() list does not raise a warning', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $query) {
        $query->where($query->meta('a')->in([]));
    })->getArguments();

    expect($args['meta_query'][0])->toMatchArray(['compare' => 'IN', 'type' => 'CHAR', 'value' => []]);
});

test('MetaQuery: calling metaQuery twice does not duplicate clauses', function () {
    $args = PostQuery::select()
        ->metaQuery(fn (MetaQuery $q) => $q->where($q->meta('a')->equalTo('x')))
        ->metaQuery(fn (MetaQuery $q) => $q->where($q->meta('b')->equalTo('y')))
        ->getArguments();

    expect(array_column(array_filter($args['meta_query'], 'is_array'), 'key'))->toBe(['a', 'b']);
});

test('MetaQuery: mixing where and orWhere groups like Eloquent', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('a')->equalTo('1'))
            ->where($q->meta('b')->equalTo('2'))
            ->orWhere($q->meta('c')->equalTo('3'));
    })->getArguments();

    $keys = fn (array $group) => array_column(array_filter($group, 'is_array'), 'key');

    expect($args['meta_query']['relation'])->toBe('OR')
        ->and($args['meta_query'][0]['relation'])->toBe('AND')
        ->and($keys($args['meta_query'][0]))->toBe(['a', 'b'])
        ->and($args['meta_query'][1]['key'])->toBe('c');
});

test('MetaQuery: a relation switch after two clauses keeps grouping left to right', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('a')->equalTo('1'))
            ->orWhere($q->meta('b')->equalTo('2'))
            ->where($q->meta('c')->equalTo('3'));
    })->getArguments();

    // ((a OR b) AND c)
    expect($args['meta_query']['relation'])->toBe('AND')
        ->and($args['meta_query'][0]['relation'])->toBe('OR')
        ->and($args['meta_query'][1]['key'])->toBe('c');
});

test('MetaQuery: a single clause just takes the requested relation', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->orWhere($q->meta('a')->equalTo('1'))->where($q->meta('b')->equalTo('2'));
    })->getArguments();

    expect($args['meta_query']['relation'])->toBe('AND')
        ->and($args['meta_query'])->toHaveCount(3);
});

test('MetaQuery: named clauses survive grouping', function () {
    $args = PostQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('a')->equalTo('1')->state('first'))
            ->where($q->meta('b')->equalTo('2'))
            ->orWhere($q->meta('c')->equalTo('3'));
    })->getArguments();

    expect($args['meta_query'][0])->toHaveKey('first');
});
