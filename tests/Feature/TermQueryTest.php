<?php

use Pollora\Query\MetaQuery;
use Pollora\Query\QueryException;
use Pollora\Query\TermQuery;

beforeEach(function () {
    WP_Term_Query::$log = [];
    WP_Term_Query::$fakeTerms = [];
});

test('TermQuery: defaults to every term, empty ones included', function () {
    expect(TermQuery::select()->getArguments())->toEqual(['hide_empty' => false, 'fields' => 'all']);
});

test('TermQuery: taxonomy and object_ids', function () {
    $args = TermQuery::select()->taxonomy(['category', 'post_tag'])->objectIds([1, 2])->getArguments();

    expect($args)->toMatchArray(['taxonomy' => ['category', 'post_tag'], 'object_ids' => [1, 2]]);
});

test('TermQuery: find maps to include', function () {
    expect(TermQuery::find(5)->getArguments())->toMatchArray(['include' => [5]])
        ->and(TermQuery::find([1, 2])->getArguments())->toMatchArray(['include' => [1, 2]]);
});

test('TermQuery: select with fields', function () {
    expect(TermQuery::select('ids')->getArguments()['fields'])->toBe('ids')
        ->and(TermQuery::select('*')->getArguments()['fields'])->toBe('all');
});

test('TermQuery: invalid fields throws', function () {
    TermQuery::select('bogus');
})->throws(QueryException::class, 'Invalid fields value supplied: bogus');

test('TermQuery: selection filters', function () {
    $args = TermQuery::select()
        ->whereIn([1, 2])->whereNotIn([3])->excludeTree([4])
        ->name('News')->slug(['news', 'press'])->termTaxonomyId(9)
        ->search('new')->nameLike('Ne')->descriptionLike('about')
        ->hideEmpty()
        ->getArguments();

    expect($args)->toMatchArray([
        'include' => [1, 2], 'exclude' => [3], 'exclude_tree' => [4],
        'name' => 'News', 'slug' => ['news', 'press'], 'term_taxonomy_id' => 9,
        'search' => 'new', 'name__like' => 'Ne', 'description__like' => 'about',
        'hide_empty' => true,
    ]);
});

test('TermQuery: withEmpty overrides hideEmpty', function () {
    expect(TermQuery::select()->hideEmpty()->withEmpty()->getArguments()['hide_empty'])->toBeFalse();
});

test('TermQuery: hierarchy', function () {
    $args = TermQuery::select()
        ->hierarchical(false)->padCounts()->unrestricted()->childOf(3)->parentId(2)->childless()
        ->getArguments();

    expect($args)->toMatchArray([
        'hierarchical' => false, 'pad_counts' => true, 'get' => 'all', 'child_of' => 3, 'parent' => 2, 'childless' => true,
    ]);
});

test('TermQuery: rootOnly is parent 0', function () {
    expect(TermQuery::select()->rootOnly()->getArguments())->toHaveKey('parent', 0);
});

test('TermQuery: order is a single field with a direction', function () {
    expect(TermQuery::select()->orderBy('slug', 'desc')->getArguments())->toMatchArray(['orderby' => 'slug', 'order' => 'DESC'])
        ->and(TermQuery::select()->orderByName()->getArguments())->toMatchArray(['orderby' => 'name', 'order' => 'ASC'])
        ->and(TermQuery::select()->orderByCount()->getArguments())->toMatchArray(['orderby' => 'count', 'order' => 'DESC'])
        ->and(TermQuery::select()->orderByInclude()->getArguments()['orderby'])->toBe('include')
        ->and(TermQuery::select()->withoutOrder()->getArguments()['orderby'])->toBe('none');
});

test('TermQuery: invalid order direction throws', function () {
    TermQuery::select()->orderBy('name', 'sideways');
})->throws(QueryException::class);

test('TermQuery: limit and offset', function () {
    expect(TermQuery::select()->limit(5)->offset(10)->getArguments())->toMatchArray(['number' => 5, 'offset' => 10])
        ->and(TermQuery::select()->take(2)->skip(4)->getArguments())->toMatchArray(['number' => 2, 'offset' => 4]);
});

test('TermQuery: cache', function () {
    $args = TermQuery::select()->cacheResults(false)->updateMetaCache(false)->cacheDomain('my-domain')->getArguments();

    expect($args)->toMatchArray(['cache_results' => false, 'update_term_meta_cache' => false, 'cache_domain' => 'my-domain'])
        ->and($args)->not->toHaveKey('update_post_meta_cache');
});

test('TermQuery: meta arguments and orderByMeta', function () {
    $args = TermQuery::select()->metaKey('color')->metaValue('red')->orderByMeta('weight', 'ASC', true)->getArguments();

    expect($args)->toMatchArray(['meta_key' => 'weight', 'meta_value' => 'red', 'orderby' => 'meta_value_num', 'order' => 'ASC']);
});

test('TermQuery: meta_query', function () {
    $args = TermQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('color')->equalTo('red'))->orWhere($q->meta('size')->greaterThan(3));
    })->getArguments();

    expect($args['meta_query']['relation'])->toBe('OR')
        ->and(array_column(array_filter($args['meta_query'], 'is_array'), 'key'))->toBe(['color', 'size'])
        ->and($args)->not->toHaveKey('query_type');
});

test('TermQuery: get runs WP_Term_Query with the arguments', function () {
    TermQuery::select()->taxonomy('genre')->get();

    expect(WP_Term_Query::$log[0])->toMatchArray(['taxonomy' => 'genre']);
});

test('TermQuery: first', function () {
    $term = new stdClass;
    WP_Term_Query::$fakeTerms = [$term, new stdClass];

    expect(TermQuery::select()->taxonomy('genre')->first())->toBe($term)
        ->and(WP_Term_Query::$log[0])->toMatchArray(['number' => 1, 'taxonomy' => 'genre']);
});

test('TermQuery: first returns null when nothing matches', function () {
    expect(TermQuery::select()->first())->toBeNull();
});

test('TermQuery: first handles keyed results and never runs a count', function () {
    WP_Term_Query::$fakeTerms = [4 => 'News'];

    expect(TermQuery::select('id=>name')->first())->toBe('News');

    TermQuery::select('count')->first();

    expect(WP_Term_Query::$log[1]['fields'])->toBe('all');
});

test('TermQuery: ids, names and slugs', function () {
    WP_Term_Query::$fakeTerms = ['3', 5];
    expect(TermQuery::select()->ids())->toBe([3, 5])
        ->and(WP_Term_Query::$log[0]['fields'])->toBe('ids');

    WP_Term_Query::$fakeTerms = ['a', 'b'];
    expect(TermQuery::select()->names())->toBe(['a', 'b'])
        ->and(WP_Term_Query::$log[1]['fields'])->toBe('names')
        ->and(TermQuery::select()->slugs())->toBe(['a', 'b'])
        ->and(WP_Term_Query::$log[2]['fields'])->toBe('slugs');
});

test('TermQuery: count ignores limit and offset', function () {
    WP_Term_Query::$fakeTerms = '12';

    expect(TermQuery::select()->limit(5)->offset(5)->count())->toBe(12)
        ->and(WP_Term_Query::$log[0])->toMatchArray(['fields' => 'count'])
        ->and(WP_Term_Query::$log[0])->not->toHaveKeys(['number', 'offset']);
});

test('TermQuery: exists', function () {
    expect(TermQuery::select()->exists())->toBeFalse();

    WP_Term_Query::$fakeTerms = [1];

    expect(TermQuery::select()->exists())->toBeTrue()
        ->and(WP_Term_Query::$log[1])->toMatchArray(['fields' => 'ids', 'number' => 1]);
});

test('TermQuery: terminals do not alter the builder', function () {
    $query = TermQuery::select()->taxonomy('genre')->limit(10)->offset(5);
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->names();
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before);
});
