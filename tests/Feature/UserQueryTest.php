<?php

use Pollora\Query\DateQuery;
use Pollora\Query\MetaQuery;
use Pollora\Query\QueryException;
use Pollora\Query\UserQuery;

beforeEach(function () {
    WP_User_Query::$log = [];
    WP_User_Query::$fakeResults = [];
    WP_User_Query::$fakeTotal = 0;
});

test('UserQuery: defaults to every user', function () {
    expect(UserQuery::select()->getArguments())->toBe(['fields' => 'all']);
});

test('UserQuery: find maps to include', function () {
    expect(UserQuery::find(5)->getArguments())->toMatchArray(['include' => [5]])
        ->and(UserQuery::find([1, 2])->getArguments())->toMatchArray(['include' => [1, 2]]);
});

test('UserQuery: roles', function () {
    $args = UserQuery::select()->role(['editor', 'author'])->roleIn(['a'])->roleNotIn(['b'])->blogId(2)->getArguments();

    expect($args)->toMatchArray(['role' => ['editor', 'author'], 'role__in' => ['a'], 'role__not_in' => ['b'], 'blog_id' => 2]);
});

test('UserQuery: capabilities', function () {
    $args = UserQuery::select()->capability('edit_posts')->capabilityIn(['a'])->capabilityNotIn(['b'])->getArguments();

    expect($args)->toMatchArray(['capability' => 'edit_posts', 'capability__in' => ['a'], 'capability__not_in' => ['b']]);
});

test('UserQuery: identity filters', function () {
    $args = UserQuery::select()
        ->whereIn([1])->whereNotIn([2])
        ->login('bob')->loginIn(['a'])->loginNotIn(['b'])
        ->nicename('bob')->nicenameIn(['c'])->nicenameNotIn(['d'])
        ->hasPublishedPosts(['post', 'page'])
        ->getArguments();

    expect($args)->toMatchArray([
        'include' => [1], 'exclude' => [2],
        'login' => 'bob', 'login__in' => ['a'], 'login__not_in' => ['b'],
        'nicename' => 'bob', 'nicename__in' => ['c'], 'nicename__not_in' => ['d'],
        'has_published_posts' => ['post', 'page'],
    ]);
});

test('UserQuery: hasPublishedPosts defaults to true', function () {
    expect(UserQuery::select()->hasPublishedPosts()->getArguments()['has_published_posts'])->toBeTrue();
});

test('UserQuery: search', function () {
    $args = UserQuery::select()->search('*bob*')->searchColumns(['user_login', 'display_name'])->getArguments();

    expect($args)->toMatchArray(['search' => '*bob*', 'search_columns' => ['user_login', 'display_name']]);
});

test('UserQuery: invalid search column throws', function () {
    UserQuery::select()->searchColumns(['user_pass']);
})->throws(QueryException::class, 'Invalid search column supplied: user_pass');

test('UserQuery: fields', function () {
    expect(UserQuery::select('id')->getArguments()['fields'])->toBe('ID')
        ->and(UserQuery::select('*')->getArguments()['fields'])->toBe('all')
        ->and(UserQuery::select(['ID', 'user_email', 'id'])->getArguments()['fields'])->toBe(['ID', 'user_email']);
});

test('UserQuery: invalid fields throws', function (string|array $fields) {
    UserQuery::select()->fields($fields);
})->with([['bogus'], [['ID', 'bogus']], [['all', 'ID']], [[]]])->throws(QueryException::class);

test('UserQuery: orderby is a map of fields and directions', function () {
    $args = UserQuery::select()->orderBy('display_name', 'desc')->orderByLogin()->orderBy(['user_email' => 'ASC'])->getArguments();

    expect($args['orderby'])->toBe(['display_name' => 'DESC', 'user_login' => 'ASC', 'user_email' => 'ASC']);
});

test('UserQuery: order shortcuts', function () {
    expect(UserQuery::select()->latest()->getArguments()['orderby'])->toBe(['user_registered' => 'DESC'])
        ->and(UserQuery::select()->oldest()->getArguments()['orderby'])->toBe(['user_registered' => 'ASC'])
        ->and(UserQuery::select()->orderByName()->getArguments()['orderby'])->toBe(['display_name' => 'ASC'])
        ->and(UserQuery::select()->orderByEmail('desc')->getArguments()['orderby'])->toBe(['user_email' => 'DESC'])
        ->and(UserQuery::select()->orderByPostCount()->getArguments()['orderby'])->toBe(['post_count' => 'DESC'])
        ->and(UserQuery::select()->orderByInclude()->orderByLoginIn()->orderByNicenameIn()->getArguments()['orderby'])
        ->toBe(['include' => 'ASC', 'login__in' => 'ASC', 'nicename__in' => 'ASC']);
});

test('UserQuery: invalid order direction throws', function () {
    UserQuery::select()->orderBy('display_name', 'sideways');
})->throws(QueryException::class);

test('UserQuery: limit, offset and paged', function () {
    expect(UserQuery::select()->limit(5)->offset(10)->paged(2)->getArguments())->toMatchArray(['number' => 5, 'offset' => 10, 'paged' => 2])
        ->and(UserQuery::select()->take(2)->skip(4)->getArguments())->toMatchArray(['number' => 2, 'offset' => 4]);
});

test('UserQuery: cache and count_total', function () {
    $args = UserQuery::select()->cacheResults(false)->withoutCount()->getArguments();

    expect($args)->toMatchArray(['cache_results' => false, 'count_total' => false])
        ->and(UserQuery::select()->countTotal()->getArguments()['count_total'])->toBeTrue();
});

test('UserQuery: meta arguments and orderByMeta', function () {
    $args = UserQuery::select()->metaKey('age')->metaCompare('>')->metaValue(18)->metaType('numeric')->orderByMeta('age', 'DESC', true)->getArguments();

    expect($args)->toMatchArray(['meta_key' => 'age', 'meta_compare' => '>', 'meta_value' => 18, 'meta_type' => 'NUMERIC'])
        ->and($args['orderby'])->toBe(['meta_value_num' => 'DESC']);
});

test('UserQuery: meta_query', function () {
    $args = UserQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('plan')->equalTo('pro'));
    })->getArguments();

    expect($args['meta_query'][0])->toMatchArray(['key' => 'plan', 'compare' => '=', 'value' => 'pro'])
        ->and($args)->not->toHaveKey('query_type');
});

test('UserQuery: date_query leaves the column to WP (user_registered)', function () {
    $args = UserQuery::select()->dateQuery(function (DateQuery $q) {
        $q->where($q->date()->after('2024-01-01'))->inclusive();
    })->getArguments();

    expect($args['date_query'][0])->not->toHaveKey('column')
        ->and($args['date_query'][0]['inclusive'])->toBeTrue();
});

test('UserQuery: get runs WP_User_Query with the arguments', function () {
    UserQuery::select()->role('editor')->get();

    expect(WP_User_Query::$log[0])->toMatchArray(['role' => 'editor']);
});

test('UserQuery: first', function () {
    $user = new stdClass;
    WP_User_Query::$fakeResults = [$user, new stdClass];

    expect(UserQuery::select()->role('editor')->first())->toBe($user)
        ->and(WP_User_Query::$log[0])->toMatchArray(['number' => 1, 'count_total' => false, 'role' => 'editor']);
});

test('UserQuery: first returns null when nothing matches and ignores paging', function () {
    expect(UserQuery::select()->offset(5)->paged(3)->first())->toBeNull()
        ->and(WP_User_Query::$log[0])->not->toHaveKeys(['offset', 'paged']);
});

test('UserQuery: ids and pluck', function () {
    WP_User_Query::$fakeResults = ['3', 5];
    expect(UserQuery::select()->ids())->toBe([3, 5])
        ->and(WP_User_Query::$log[0])->toMatchArray(['fields' => 'ID', 'count_total' => false]);

    WP_User_Query::$fakeResults = ['a@x.test'];
    expect(UserQuery::select()->pluck('user_email'))->toBe(['a@x.test'])
        ->and(WP_User_Query::$log[1]['fields'])->toBe('user_email');
});

test('UserQuery: pluck rejects all and unknown fields', function () {
    expect(fn () => UserQuery::select()->pluck('all'))->toThrow(QueryException::class)
        ->and(fn () => UserQuery::select()->pluck('bogus'))->toThrow(QueryException::class);
});

test('UserQuery: count ignores limit, offset and page', function () {
    WP_User_Query::$fakeTotal = 42;

    expect(UserQuery::select()->limit(10)->offset(5)->paged(3)->withoutCount()->count())->toBe(42)
        ->and(WP_User_Query::$log[0])->toMatchArray(['fields' => 'ID', 'number' => 1, 'count_total' => true])
        ->and(WP_User_Query::$log[0])->not->toHaveKeys(['offset', 'paged']);
});

test('UserQuery: exists', function () {
    expect(UserQuery::select()->exists())->toBeFalse();

    WP_User_Query::$fakeResults = [1];

    expect(UserQuery::select()->exists())->toBeTrue()
        ->and(WP_User_Query::$log[1])->toMatchArray(['fields' => 'ID', 'number' => 1]);
});

test('UserQuery: terminals do not alter the builder', function () {
    $query = UserQuery::select()->role('editor')->limit(10)->offset(5)->paged(2);
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->pluck('user_email');
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before);
});
