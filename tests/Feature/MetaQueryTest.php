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
