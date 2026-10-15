<?php

use Pollora\Query\DateQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\QueryException;

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
                    'hour' => 0,
                    'minute' => 0,
                    'second' => 0,
                ],
                'after' => [
                    'year' => 2021,
                    'month' => 1,
                    'day' => 1,
                ],
                'inclusive' => true,
            ],
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
                    'hour' => 0,
                    'minute' => 0,
                    'second' => 0,
                ],
                'after' => [
                    'year' => 2021,
                    'month' => 1,
                    'day' => 1,
                    'hour' => 0,
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
                    'hour' => 0,
                    'minute' => 0,
                    'second' => 0,
                ],
            ],
        ],
    ]);
});

test('DateQuery: time is extracted on a 24-hour clock', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->before('2024-03-05 15:10:20'));
    })->getArguments();

    expect($args['date_query'][0]['before'])->toBe([
        'year' => 2024, 'month' => 3, 'day' => 5, 'hour' => 15, 'minute' => 10, 'second' => 20,
    ]);
});

test('DateQuery: no output leaks while building', function () {
    ob_start();
    PostQuery::select()->dateQuery(fn (DateQuery $query) => $query->where($query->date()->after('2024-03-05')))->getArguments();

    expect(ob_get_clean())->toBe('');
});

test('DateQuery: column defaults to post_date when no key is given', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-03-05'));
    })->getArguments();

    expect($args['date_query'][0]['column'])->toBe('post_date');
});

test('DateQuery: within an array', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->within(['year' => 2024, 'month' => '03']));
    })->getArguments();

    expect($args['date_query'][0])->toBe(['column' => 'post_date', 'year' => 2024, 'month' => 3]);
});

test('DateQuery: within a date string matches the whole day', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->within('2024-03-05'));
    })->getArguments();

    expect($args['date_query'][0])->toBe(['column' => 'post_date', 'year' => 2024, 'month' => 3, 'day' => 5]);
});

test('DateQuery: within rejects unknown keys', function () {
    PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->within(['century' => 21]));
    });
})->throws(QueryException::class);

test('DateQuery: a numeric timestamp is accepted', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after((string) mktime(10, 0, 0, 3, 5, 2024)));
    })->getArguments();

    expect($args['date_query'][0]['after'])->toMatchArray(['year' => 2024, 'month' => 3, 'day' => 5, 'hour' => 10]);
});

test('DateQuery: inclusive is applied on every clause, not at the query root', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-01-01'))->inclusive()->orWhere($query->date()->before('2024-06-01'));
    })->getArguments();

    expect($args['date_query'])->not->toHaveKey('inclusive')
        ->and($args['date_query'][0]['inclusive'])->toBeTrue()
        ->and($args['date_query'][1]['inclusive'])->toBeTrue();
});

test('DateQuery: inclusive can be set on a single clause', function () {
    $args = PostQuery::select()->dateQuery(function (DateQuery $query) {
        $query->where($query->date()->after('2024-01-01')->inclusive())->where($query->date()->before('2024-06-01'));
    })->getArguments();

    expect($args['date_query'][0]['inclusive'])->toBeTrue()
        ->and($args['date_query'][1])->not->toHaveKey('inclusive');
});
