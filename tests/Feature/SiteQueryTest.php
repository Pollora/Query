<?php

use Pollora\Query\DateQuery;
use Pollora\Query\MetaQuery;
use Pollora\Query\QueryException;
use Pollora\Query\SiteQuery;

beforeEach(function () {
    WP_Site_Query::$log = [];
    WP_Site_Query::$fakeResults = [];
    WP_Site_Query::$fakeCount = 0;
});

test('SiteQuery: removes the 100 sites cap of WP', function () {
    expect(SiteQuery::select()->getArguments())->toEqual(['number' => 0]);
});

test('SiteQuery: limit restores a cap', function () {
    expect(SiteQuery::select()->limit(20)->offset(5)->getArguments())->toEqual(['number' => 20, 'offset' => 5]);
});

test('SiteQuery: find maps to site__in', function () {
    expect(SiteQuery::find(3)->getArguments())->toMatchArray(['site__in' => [3]])
        ->and(SiteQuery::find([1, 2])->getArguments())->toMatchArray(['site__in' => [1, 2]]);
});

test('SiteQuery: site and network filters', function () {
    $args = SiteQuery::select()->whereIn([1])->whereNotIn([2])->networkId(4)->networkIn([5])->networkNotIn([6])->getArguments();

    expect($args)->toMatchArray(['site__in' => [1], 'site__not_in' => [2], 'network_id' => 4, 'network__in' => [5], 'network__not_in' => [6]]);
});

test('SiteQuery: domain and path', function () {
    $args = SiteQuery::select()
        ->domain('a.test')->domainIn(['b.test'])->domainNotIn(['c.test'])
        ->path('/blog/')->pathIn(['/x/'])->pathNotIn(['/y/'])
        ->getArguments();

    expect($args)->toMatchArray([
        'domain' => 'a.test', 'domain__in' => ['b.test'], 'domain__not_in' => ['c.test'],
        'path' => '/blog/', 'path__in' => ['/x/'], 'path__not_in' => ['/y/'],
    ]);
});

test('SiteQuery: status flags are sent as 1 or 0, and only when asked', function () {
    expect(SiteQuery::select()->getArguments())->not->toHaveKeys(['public', 'archived', 'mature', 'spam', 'deleted'])
        ->and(SiteQuery::select()->wherePublic()->whereArchived(false)->whereMature()->whereSpam(false)->whereDeleted()->getArguments())
        ->toMatchArray(['public' => 1, 'archived' => 0, 'mature' => 1, 'spam' => 0, 'deleted' => 1]);
});

test('SiteQuery: languages', function () {
    expect(SiteQuery::select()->langId(2)->langIn(['1', '3'])->langNotIn(['4'])->getArguments())
        ->toMatchArray(['lang_id' => 2, 'lang__in' => ['1', '3'], 'lang__not_in' => ['4']]);
});

test('SiteQuery: search', function () {
    expect(SiteQuery::select()->search('*blog*')->searchColumns(['domain'])->getArguments())
        ->toMatchArray(['search' => '*blog*', 'search_columns' => ['domain']]);
});

test('SiteQuery: invalid search column throws', function () {
    SiteQuery::select()->searchColumns(['blogname']);
})->throws(QueryException::class, 'Invalid search column supplied: blogname');

test('SiteQuery: fields', function () {
    expect(SiteQuery::select('ids')->getArguments()['fields'])->toBe('ids')
        ->and(SiteQuery::select('*')->getArguments())->not->toHaveKey('fields');
});

test('SiteQuery: invalid fields throws', function () {
    SiteQuery::select('names');
})->throws(QueryException::class, 'Invalid fields value supplied: names');

test('SiteQuery: orderby is a map of fields and directions', function () {
    $args = SiteQuery::select()->orderByDomain('desc')->orderByPath()->latest()->getArguments();

    expect($args['orderby'])->toBe(['domain' => 'DESC', 'path' => 'ASC', 'registered' => 'DESC']);
});

test('SiteQuery: order shortcuts', function () {
    expect(SiteQuery::select()->oldest()->getArguments()['orderby'])->toBe(['registered' => 'ASC'])
        ->and(SiteQuery::select()->orderByLastUpdated()->getArguments()['orderby'])->toBe(['last_updated' => 'DESC'])
        ->and(SiteQuery::select()->orderByDomainLength()->orderByPathLength()->getArguments()['orderby'])->toBe(['domain_length' => 'ASC', 'path_length' => 'ASC'])
        ->and(SiteQuery::select()->orderByInclude()->orderByNetworkIn()->getArguments()['orderby'])->toBe(['site__in' => 'ASC', 'network__in' => 'ASC'])
        ->and(SiteQuery::select()->withoutOrder()->getArguments()['orderby'])->toBe('none');
});

test('SiteQuery: invalid order direction throws', function () {
    SiteQuery::select()->orderByDomain('sideways');
})->throws(QueryException::class);

test('SiteQuery: cache and found rows', function () {
    $args = SiteQuery::select()->noFoundRows(false)->updateCache(false)->updateMetaCache(false)->getArguments();

    expect($args)->toMatchArray(['no_found_rows' => false, 'update_site_cache' => false, 'update_site_meta_cache' => false]);
});

test('SiteQuery: meta and meta_query reuse the shared builders', function () {
    $args = SiteQuery::select()
        ->metaKey('plan')->metaValue('pro')
        ->metaQuery(function (MetaQuery $q) {
            $q->where($q->meta('seats')->greaterThan(10));
        })
        ->getArguments();

    expect($args)->toMatchArray(['meta_key' => 'plan', 'meta_value' => 'pro'])
        ->and($args['meta_query'][0])->toMatchArray(['key' => 'seats', 'compare' => '>', 'value' => 10]);
});

test('SiteQuery: date_query leaves the column to WP (registered)', function () {
    $args = SiteQuery::select()->dateQuery(function (DateQuery $q) {
        $q->where($q->date()->after('2024-01-01'));
    })->getArguments();

    expect($args['date_query'][0])->not->toHaveKey('column');
});

test('SiteQuery: get runs WP_Site_Query with the arguments', function () {
    $query = SiteQuery::select()->domain('a.test')->get();

    expect(WP_Site_Query::$log[0])->toMatchArray(['domain' => 'a.test'])
        ->and($query)->toBeInstanceOf(WP_Site_Query::class);
});

test('SiteQuery: first', function () {
    $site = new stdClass;
    WP_Site_Query::$fakeResults = [$site, new stdClass];

    expect(SiteQuery::select()->domain('a.test')->first())->toBe($site)
        ->and(WP_Site_Query::$log[0])->toMatchArray(['number' => 1, 'domain' => 'a.test']);
});

test('SiteQuery: first returns null when nothing matches', function () {
    expect(SiteQuery::select()->offset(5)->first())->toBeNull()
        ->and(WP_Site_Query::$log[0])->not->toHaveKey('offset');
});

test('SiteQuery: ids', function () {
    WP_Site_Query::$fakeResults = ['3', 5];

    expect(SiteQuery::select()->ids())->toBe([3, 5])
        ->and(WP_Site_Query::$log[0])->toMatchArray(['fields' => 'ids', 'number' => 0]);
});

test('SiteQuery: count ignores the limit and the offset', function () {
    WP_Site_Query::$fakeCount = 12;

    expect(SiteQuery::select()->limit(5)->offset(2)->count())->toBe(12)
        ->and(WP_Site_Query::$log[0])->toMatchArray(['count' => true])
        ->and(WP_Site_Query::$log[0])->not->toHaveKeys(['number', 'offset']);
});

test('SiteQuery: exists', function () {
    expect(SiteQuery::select()->exists())->toBeFalse();

    WP_Site_Query::$fakeResults = [1];

    expect(SiteQuery::select()->exists())->toBeTrue()
        ->and(WP_Site_Query::$log[1])->toMatchArray(['fields' => 'ids', 'number' => 1]);
});

test('SiteQuery: terminals do not alter the builder', function () {
    $query = SiteQuery::select()->domain('a.test')->limit(10)->offset(5);
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before)->and($before)->not->toHaveKey('count');
});
