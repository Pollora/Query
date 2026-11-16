<?php

use Pollora\Query\DateQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\Query;
use Pollora\Query\QueryBuilder\DateQueryBuilder;
use Pollora\Query\QueryBuilder\TaxQueryBuilder;
use Pollora\Query\QueryException;
use Pollora\Query\Utils\ValueTypeDetector;

beforeEach(function () {
    WP_Query::$log = [];
    WP_Query::$fakePosts = [];
});

test('DateQuery: an unknown key in a date array names itself in the error', function () {
    (new DateQueryBuilder)->within(['year' => 2021, 'bogus' => 1]);
})->throws(QueryException::class, 'Invalid key bogus element supplied.');

test('DateQuery: a string that is not a date is refused', function (string $value) {
    (new DateQueryBuilder)->before($value);
})->throws(QueryException::class, 'Provided datestring could not be converted to time')
    ->with(['not a date', 'garbage in', '32/13/2021 abc']);

test('DateQuery: extracting keeps only the parts asked for', function (string $extract, array $expected) {
    expect((new DateQueryBuilder('c'))->extractFromDate('2021-03-04 15:06:07', $extract))->toBe($expected);
})->with([
    'minute' => ['i', ['minute' => 6]],
    'second' => ['s', ['second' => 7]],
    'year' => ['Y', ['year' => 2021]],
    'month' => ['m', ['month' => 3]],
    'day' => ['d', ['day' => 4]],
    'hour' => ['H', ['hour' => 15]],
    'hour as a lowercase alias' => ['h', ['hour' => 15]],
    'everything' => ['YmdHis', ['year' => 2021, 'month' => 3, 'day' => 4, 'hour' => 15, 'minute' => 6, 'second' => 7]],
]);

test('DateQuery: a clause with a column and only a before date', function () {
    $clause = PostQuery::select()->dateQuery(fn (DateQuery $q) => $q->where($q->date('c')->before('2022-01-01')))->getArguments()['date_query'][0];

    expect($clause)->toBe(['column' => 'c', 'before' => ['year' => 2022, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0]]);
});

test('DateQuery: a clause with a column and only an after date', function () {
    $clause = PostQuery::select()->dateQuery(fn (DateQuery $q) => $q->where($q->date('c')->after('2022-01-01')))->getArguments()['date_query'][0];

    expect($clause)->toBe(['column' => 'c', 'after' => ['year' => 2022, 'month' => 1, 'day' => 1, 'hour' => 0, 'minute' => 0, 'second' => 0]]);
});

test('DateQuery: a clause built from the parts of a date keeps the column and every part', function () {
    $clause = PostQuery::select()->dateQuery(fn (DateQuery $q) => $q->where($q->date('c')->within('2021-03-04 15:06:07')))->getArguments()['date_query'][0];

    expect($clause)->toBe(['column' => 'c', 'year' => 2021, 'month' => 3, 'day' => 4, 'hour' => 15, 'minute' => 6, 'second' => 7]);
});

test('TaxQuery: each way of searching a term sets the matching field', function (string $method, string $field) {
    $clause = PostQuery::select()->taxQuery(Query::tax('genre')->{$method}()->contains([1]))->getArguments()['tax_query'][0];

    expect($clause['field'])->toBe($field);
})->with([
    'by id' => ['searchById', 'term_id'],
    'by slug' => ['searchByTermSlug', 'slug'],
    'by name' => ['searchByTermName', 'name'],
    'by term taxonomy id' => ['searchByTermTaxId', 'term_taxonomy_id'],
]);

test('TaxQuery: field() accepts the four fields WordPress knows and refuses the others', function (string $field) {
    expect(Query::tax('genre')->field($field))->toBeInstanceOf(TaxQueryBuilder::class);
})->with(['term_id', 'name', 'term_taxonomy_id', 'slug']);

test('TaxQuery: an unknown field names itself in the error', function () {
    Query::tax('genre')->field('bogus');
})->throws(QueryException::class, 'Invalid tax field type supplied: bogus');

test('ValueTypeDetector: integers are signed only below zero, and 0 and 1 are booleans first', function (int $value, string $type) {
    expect((new ValueTypeDetector($value))->detect())->toBe($type);
})->with([
    'zero' => [0, 'BINARY'],
    'one' => [1, 'BINARY'],
    'two' => [2, 'UNSIGNED'],
    'minus one' => [-1, 'SIGNED'],
    'minus two' => [-2, 'SIGNED'],
]);

test('ValueTypeDetector: a scalar gets the type of what it holds', function (mixed $value, string $type) {
    expect((new ValueTypeDetector($value))->detect())->toBe($type);
})->with([
    'float' => [1.5, 'DECIMAL'],
    'numeric string' => ['12', 'NUMERIC'],
    'null' => [null, 'CHAR'],
    'text' => ['abc', 'CHAR'],
    'datetime' => ['2021-03-04 15:06:07', 'DATETIME'],
]);

test('ValueTypeDetector: a list takes the widest numeric type, or CHAR as soon as one value is not numeric', function (array $values, string $type) {
    expect((new ValueTypeDetector($values))->detect())->toBe($type);
})->with([
    'same type' => [[1, 2, 3], 'UNSIGNED'],
    'with a negative' => [[1, -2], 'SIGNED'],
    'with a float' => [[1, 2.5], 'DECIMAL'],
    'with a numeric string' => [[1, '2'], 'NUMERIC'],
    'numeric string and negative' => [['2', -1], 'NUMERIC'],
    'float over numeric string' => [['2', 1.5], 'DECIMAL'],
    'with text' => [[1, 'x'], 'CHAR'],
    'empty' => [[], 'CHAR'],
]);

test('ValueTypeDetector: a forced type wins over what the value is', function () {
    expect((new ValueTypeDetector(1, 'CHAR'))->detect())->toBe('CHAR');
});

test('Retrieval: exists ignores paging and sticky posts, like count', function () {
    PostQuery::select()->noPaging()->offset(5)->paged(3)->page(2)->exists();

    expect(WP_Query::$log[0])->toLackKeys(['nopaging', 'offset', 'paged', 'page'])
        ->and(WP_Query::$log[0]['ignore_sticky_posts'])->toBeTrue();
});

test('Retrieval: count ignores paging at the PostQuery level too', function () {
    PostQuery::select()->noPaging()->offset(5)->paged(3)->page(2)->count();

    expect(WP_Query::$log[0])->toLackKeys(['nopaging', 'offset', 'paged', 'page'])
        ->and(WP_Query::$log[0]['ignore_sticky_posts'])->toBeTrue();
});
