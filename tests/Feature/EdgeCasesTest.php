<?php

declare(strict_types=1);

use Pollora\Query\CommentQuery;
use Pollora\Query\DateQuery;
use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\Query;
use Pollora\Query\QueryBuilder\DateQueryBuilder;
use Pollora\Query\QueryBuilder\TaxQueryBuilder;
use Pollora\Query\QueryException;
use Pollora\Query\SiteQuery;
use Pollora\Query\TermQuery;
use Pollora\Query\UserQuery;
use Pollora\Query\Utils\ValueTypeDetector;

beforeEach(function () {
    WP_Query::$log = [];
    WP_Query::$fakePosts = [];
    WP_Term_Query::$log = [];
    WP_Term_Query::$fakeTerms = [];
    WP_User_Query::$log = [];
    WP_User_Query::$fakeResults = [];
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
    'float' => [1.5, 'DECIMAL(30,10)'],
    'float with a long fraction' => [0.123456789012, 'DECIMAL(32,12)'],
    'float with more decimals than a decimal holds' => [0.1234567890123456, 'DECIMAL(35,15)'],
    'string with a fraction' => ['1.5', 'DECIMAL(30,10)'],
    'string with a zero fraction' => ['2.0', 'NUMERIC'],
    'string with an exponent' => ['1.5e-3', 'DECIMAL(30,10)'],
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
    'with a float' => [[1, 2.5], 'DECIMAL(30,10)'],
    'floats of different scales' => [[1.5, 0.123456789012], 'DECIMAL(32,12)'],
    'with a numeric string' => [[1, '2'], 'NUMERIC'],
    'numeric string and negative' => [['2', -1], 'NUMERIC'],
    'float over numeric string' => [['2', 1.5], 'DECIMAL(30,10)'],
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

test('TermQuery: ids, names and slugs come back as lists whatever the keys WordPress gives them', function () {
    WP_Term_Query::$fakeTerms = [5 => 'a', 9 => 'b'];

    expect(TermQuery::select()->names())->toBe(['a', 'b'])
        ->and(TermQuery::select()->slugs())->toBe(['a', 'b']);

    WP_Term_Query::$fakeTerms = [5 => '3', 9 => '4'];

    expect(TermQuery::select()->ids())->toBe([3, 4]);
});

test('UserQuery: users come back as a list whatever the keys WordPress gives them', function () {
    WP_User_Query::$fakeResults = [4 => 'a', 8 => 'b'];

    expect(UserQuery::select()->pluck('user_email'))->toBe(['a', 'b']);
});

test('UserQuery: pluck and exists do not ask for a total', function () {
    WP_User_Query::$fakeResults = ['a@x.test'];

    UserQuery::select()->pluck('user_email');
    UserQuery::select()->exists();

    expect(WP_User_Query::$log[0]['count_total'])->toBeFalse()
        ->and(WP_User_Query::$log[1]['count_total'])->toBeFalse();
});

test('UserQuery: pluck refuses the wildcard as well as all', function () {
    UserQuery::select()->pluck('*');
})->throws(QueryException::class, "pluck() expects a single field, 'all' returns whole users: use get().");

test('SiteQuery: both search columns are accepted', function (string $column) {
    expect(SiteQuery::select()->searchColumns([$column])->getArguments()['search_columns'])->toBe([$column]);
})->with(['domain', 'path']);

test('CommentQuery: hierarchical takes true as a mode that WordPress does not know, and says so', function () {
    CommentQuery::select()->hierarchical(true);
})->throws(QueryException::class, 'Invalid hierarchical mode supplied: 1');

test('MetaQuery: a type is written in capitals whatever the case it is given in', function () {
    $clause = PostQuery::select()->metaQuery(fn (MetaQuery $q) => $q->where($q->meta('p')->ofType('numeric')->equalTo('5')))->getArguments()['meta_query'][0];

    expect($clause['type'])->toBe('NUMERIC');
});
