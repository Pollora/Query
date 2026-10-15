<?php

use Pollora\Query\NetworkQuery;
use Pollora\Query\QueryException;

beforeEach(function () {
    WP_Network_Query::$log = [];
    WP_Network_Query::$fakeResults = [];
    WP_Network_Query::$fakeCount = 0;
});

test('NetworkQuery: applies no constraint of its own', function () {
    expect(NetworkQuery::select()->getArguments())->toEqual([]);
});

test('NetworkQuery: find maps to network__in', function () {
    expect(NetworkQuery::find(2)->getArguments())->toEqual(['network__in' => [2]])
        ->and(NetworkQuery::find([1, 2])->getArguments())->toEqual(['network__in' => [1, 2]]);
});

test('NetworkQuery: filters', function () {
    $args = NetworkQuery::select()
        ->whereIn([1])->whereNotIn([2])
        ->domain('a.test')->domainIn(['b.test'])->domainNotIn(['c.test'])
        ->path('/')->pathIn(['/x/'])->pathNotIn(['/y/'])
        ->search('*test*')
        ->getArguments();

    expect($args)->toEqual([
        'network__in' => [1], 'network__not_in' => [2],
        'domain' => 'a.test', 'domain__in' => ['b.test'], 'domain__not_in' => ['c.test'],
        'path' => '/', 'path__in' => ['/x/'], 'path__not_in' => ['/y/'],
        'search' => '*test*',
    ]);
});

test('NetworkQuery: limit and offset', function () {
    expect(NetworkQuery::select()->limit(3)->skip(1)->getArguments())->toEqual(['number' => 3, 'offset' => 1]);
});

test('NetworkQuery: fields', function () {
    expect(NetworkQuery::select('ids')->getArguments())->toEqual(['fields' => 'ids'])
        ->and(NetworkQuery::select('all')->getArguments())->toEqual([]);
});

test('NetworkQuery: invalid fields throws', function () {
    NetworkQuery::select('names');
})->throws(QueryException::class);

test('NetworkQuery: order', function () {
    expect(NetworkQuery::select()->orderByDomain('desc')->orderByPathLength()->getArguments()['orderby'])
        ->toBe(['domain' => 'DESC', 'path_length' => 'ASC'])
        ->and(NetworkQuery::select()->orderByInclude()->getArguments()['orderby'])->toBe(['network__in' => 'ASC'])
        ->and(NetworkQuery::select()->orderByPath()->orderByDomainLength()->getArguments()['orderby'])->toBe(['path' => 'ASC', 'domain_length' => 'ASC'])
        ->and(NetworkQuery::select()->withoutOrder()->getArguments()['orderby'])->toBe('none');
});

test('NetworkQuery: cache and found rows', function () {
    expect(NetworkQuery::select()->noFoundRows(false)->updateCache(false)->getArguments())
        ->toEqual(['no_found_rows' => false, 'update_network_cache' => false]);
});

test('NetworkQuery: has no meta, date or paging methods', function () {
    $query = NetworkQuery::select();

    expect(method_exists($query, 'metaQuery'))->toBeFalse()
        ->and(method_exists($query, 'dateQuery'))->toBeFalse()
        ->and(method_exists($query, 'paged'))->toBeFalse();
});

test('NetworkQuery: get runs WP_Network_Query with the arguments', function () {
    $query = NetworkQuery::select()->domain('a.test')->get();

    expect(WP_Network_Query::$log[0])->toEqual(['domain' => 'a.test'])
        ->and($query)->toBeInstanceOf(WP_Network_Query::class);
});

test('NetworkQuery: first', function () {
    $network = new stdClass;
    WP_Network_Query::$fakeResults = [$network, new stdClass];

    expect(NetworkQuery::select()->domain('a.test')->first())->toBe($network)
        ->and(WP_Network_Query::$log[0])->toEqual(['domain' => 'a.test', 'number' => 1]);
});

test('NetworkQuery: ids', function () {
    WP_Network_Query::$fakeResults = ['2', 4];

    expect(NetworkQuery::select()->limit(5)->ids())->toEqual([2, 4])
        ->and(WP_Network_Query::$log[0])->toEqual(['fields' => 'ids', 'number' => 5]);
});

test('NetworkQuery: count ignores the limit and the offset', function () {
    WP_Network_Query::$fakeCount = 3;

    expect(NetworkQuery::select()->limit(5)->offset(2)->count())->toBe(3)
        ->and(WP_Network_Query::$log[0])->toEqual(['count' => true]);
});

test('NetworkQuery: exists', function () {
    expect(NetworkQuery::select()->exists())->toBeFalse();

    WP_Network_Query::$fakeResults = [1];

    expect(NetworkQuery::select()->exists())->toBeTrue();
});

test('NetworkQuery: terminals do not alter the builder', function () {
    $query = NetworkQuery::select()->domain('a.test')->limit(10)->offset(5);
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before);
});
