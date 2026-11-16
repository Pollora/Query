<?php

use Pollora\Query\QueryException;
use Pollora\Query\TermQuery;
use Pollora\Query\UserQuery;

dataset('term fields', [
    'all', 'all_with_object_id', 'ids', 'tt_ids', 'names', 'slugs', 'count', 'id=>parent', 'id=>name', 'id=>slug',
]);

dataset('user fields', [
    'all', 'ID', 'display_name', 'user_login', 'user_nicename', 'user_email', 'user_url',
    'user_registered', 'user_pass', 'user_activation_key', 'user_status', 'spam', 'deleted',
]);

dataset('user search columns', ['ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name']);

test('TermQuery: every field WP_Term_Query returns is accepted', function (string $field) {
    expect(TermQuery::select($field)->getArguments()['fields'])->toBe($field);
})->with('term fields');

test('TermQuery: fields() takes the same values as select()', function (string $field) {
    expect(TermQuery::select()->fields($field)->getArguments()['fields'])->toBe($field);
})->with('term fields');

test('UserQuery: every field WP_User_Query returns is accepted', function (string $field) {
    expect(UserQuery::select()->fields($field)->getArguments()['fields'])->toBe($field);
})->with('user fields');

test('UserQuery: a field is matched without regard to case and written back as WordPress spells it', function (string $field) {
    expect(UserQuery::select()->fields(strtolower($field))->getArguments()['fields'])->toBe($field)
        ->and(UserQuery::select()->fields(strtoupper($field))->getArguments()['fields'])->toBe($field);
})->with('user fields');

test('UserQuery: several fields give back only those fields', function () {
    expect(UserQuery::select()->fields(['ID', 'user_login'])->getArguments()['fields'])->toBe(['ID', 'user_login']);
});

test('UserQuery: an unknown field names itself in the error', function () {
    UserQuery::select()->fields('bogus');
})->throws(QueryException::class, 'Invalid fields value supplied: bogus');

test('UserQuery: an unknown field among several is refused', function () {
    UserQuery::select()->fields(['ID', 'bogus']);
})->throws(QueryException::class, 'Invalid fields value supplied: bogus');

test('UserQuery: every search column is accepted', function (string $column) {
    expect(UserQuery::select()->searchColumns([$column])->getArguments()['search_columns'])->toBe([$column]);
})->with('user search columns');

test('UserQuery: an unknown search column names itself in the error', function () {
    UserQuery::select()->searchColumns(['user_login', 'bogus']);
})->throws(QueryException::class, 'Invalid search column supplied: bogus');
