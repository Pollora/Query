<?php

declare(strict_types=1);

use Pollora\Query\PostQuery;
use Pollora\Query\Query;
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

test('TaxQuery: calling taxQuery twice does not duplicate clauses', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->contains([1])))
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('year')->contains([2])))
        ->getArguments();

    expect(array_column(array_filter($args['tax_query'], 'is_array'), 'taxonomy'))->toBe(['genre', 'year']);
});

test('TaxQuery: no builder state leaks into the arguments', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->contains([1])))
        ->getArguments();

    expect($args)->toLackKeys(['query_builder', 'query_type']);
});

test('TaxQuery: containsAll uses the AND operator', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->containsAll([1, 2])))
        ->getArguments();

    expect($args['tax_query'][0])->toBe([
        'relation' => 'AND',
        ['taxonomy' => 'genre', 'field' => 'term_id', 'terms' => [1], 'operator' => 'IN', 'include_children' => true],
        ['taxonomy' => 'genre', 'field' => 'term_id', 'terms' => [2], 'operator' => 'IN', 'include_children' => true],
    ]);
});

test('TaxQuery: containsAll without children is the single AND clause', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->containsAll([1, 2])->excludeChildren()))
        ->getArguments();

    expect($args['tax_query'][0])->toBe(['taxonomy' => 'genre', 'field' => 'term_id', 'terms' => [1, 2], 'operator' => 'AND', 'include_children' => false]);
});

test('TaxQuery: containsAll with one term and children is a plain IN clause', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->containsAll([1])))
        ->getArguments();

    expect($args['tax_query'][0])->toMatchArray(['operator' => 'IN', 'terms' => [1], 'include_children' => true]);
});

test('TaxQuery: containsAll with no term keeps the AND clause WordPress ignores', function () {
    $args = PostQuery::select()
        ->taxQuery(fn (TaxQuery $q) => $q->where($q->taxonomy('genre')->containsAll([])))
        ->getArguments();

    expect($args['tax_query'][0])->toMatchArray(['operator' => 'AND', 'terms' => []]);
});

test('TaxQuery: Query::tax builds a clause like the closure builder', function () {
    $args = PostQuery::select()
        ->taxQuery(Query::tax('genre')->searchByTermSlug()->contains(['jazz']))
        ->getArguments();

    expect($args['tax_query'][0])->toMatchArray(['taxonomy' => 'genre', 'field' => 'slug', 'terms' => ['jazz'], 'operator' => 'IN']);
});
