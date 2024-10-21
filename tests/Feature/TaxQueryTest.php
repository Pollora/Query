<?php

use Pollora\Query\PostQuery;
use Pollora\Query\TaxQuery;

test('TaxQuery: tax_query (simple)', function () {
    $args = PostQuery::select()->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query
                ->taxonomy('category')
                ->contains(['tee-shirt', 'sportswear'])
                ->searchByTermSlug()
        );
    })->getArguments();

    expect($args)->toMatchArray([
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => [
                    'tee-shirt',
                    'sportswear',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
        ],
    ]);
});

test('TaxQuery: tax_query (or relation)', function () {
    $args = PostQuery::select()->taxQuery(function (TaxQuery $query) {
        $query->where(
            $query
                ->taxonomy('category')
                ->contains([10, 20])
                ->searchById()
        )->orWhere(
            $query
                ->taxonomy('event')
                ->contains(['black-friday', 'christmas-sales'])
                ->searchByTermSlug()
        );
    })->getArguments();

    expect($args)->toMatchArray([
        'tax_query' => [
            'relation' => 'OR',
            [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => [
                    10,
                    20,
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
            [
                'taxonomy' => 'event',
                'field' => 'slug',
                'terms' => [
                    'black-friday',
                    'christmas-sales',
                ],
                'operator' => 'IN',
                'include_children' => true,
            ],
        ],
    ]);
});

test('TaxQuery: tax_query (nested)', function () {
    $args = PostQuery::select()->taxQuery(function (TaxQuery $query) {
        $query
            ->where(
                $query->taxonomy('status')->notContains(['private'])
            )
            ->orWhere(function (TaxQuery $query) {
                $query
                    ->where(
                        $query->taxonomy('attributes')->exists()
                    )
                    ->orWhere(
                        $query->taxonomy('product_cat')->contains(['tee-shirt', 'sportswear'])
                    );
            })
            ->orWhere(function (TaxQuery $query) {
                $query
                    ->where(
                        $query->taxonomy('promo_cat')->contains(['summer', 'blackfriday'])
                    )
                    ->andWhere(
                        $query->taxonomy('new_products')->notExists()
                    );
            });

    })->getArguments();

    expect($args)->toMatchArray([
        'tax_query' => [
            'relation' => 'OR',
            [
                'taxonomy' => 'status',
                'field' => 'term_id',
                'terms' => [
                    'private',
                ],
                'operator' => 'NOT IN',
                'include_children' => true,
            ],
            [
                'relation' => 'OR',
                [
                    'taxonomy' => 'attributes',
                    'field' => 'term_id',
                    'terms' => null,
                    'operator' => 'EXISTS',
                    'include_children' => true,
                ],
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => [
                        'tee-shirt',
                        'sportswear',
                    ],
                    'operator' => 'IN',
                    'include_children' => true,
                ],
            ],
            [
                'relation' => 'AND',
                [
                    'taxonomy' => 'promo_cat',
                    'field' => 'term_id',
                    'terms' => [
                        'summer',
                        'blackfriday',
                    ],
                    'operator' => 'IN',
                    'include_children' => true,
                ],
                [
                    'taxonomy' => 'new_products',
                    'field' => 'term_id',
                    'terms' => null,
                    'operator' => 'NOT EXISTS',
                    'include_children' => true,
                ],
            ],
        ],
    ]);
});
