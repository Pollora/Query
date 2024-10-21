<?php

use Pollora\Query\DateQuery;
use Pollora\Query\PostQuery;

test('DateQuery: date_query (simple)', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query
            ->where(
                $query->date('edited_at')->before('2022-01-01')->after([
                    'year' => '2021',
                    'month' => '01',
                    'day' => '01',
                ])
            )
            ->inclusive();
    })->getArguments();

    expect($args)->toMatchArray([
        'date_query' => [
            'relation' => 'AND',
            [
                'column' => 'edited_at',
                'before' => [
                    'year' => 2022,
                    'month' => 1,
                    'day' => 1,
                    'hour' => 12,
                    'minute' => 0,
                    'second' => 0,
                ],
                'after' => [
                    'year' => 2021,
                    'month' => 1,
                    'day' => 1,
                ],
            ],
            'inclusive' => true,
        ],
    ]
    );
});

test('DateQuery: date_query (or relation)', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where(
            $query
                ->date('posted_at')
                ->between('2021-01-01', '2022-02-01')
        )->orWhere(
            $query->date()->created()->after('2021-01-01')
        );
    })->getArguments();

    expect($args)->toMatchArray([
        'date_query' => [
            'relation' => 'OR',
            [
                'column' => 'posted_at',
                'before' => [
                    'year' => 2022,
                    'month' => 2,
                    'day' => 1,
                    'hour' => 12,
                    'minute' => 0,
                    'second' => 0,
                ],
                'after' => [
                    'year' => 2021,
                    'month' => 1,
                    'day' => 1,
                    'hour' => 12,
                    'minute' => 0,
                    'second' => 0,
                ],
            ],
            [
                'column' => 'post_date',
                'after' => [
                    'year' => 2021,
                    'month' => 01,
                    'day' => 01,
                    'hour' => 12,
                    'minute' => 0,
                    'second' => 0,
                ],
            ],
        ],
    ]);
});
