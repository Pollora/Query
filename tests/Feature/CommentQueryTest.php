<?php

use Pollora\Query\CommentQuery;
use Pollora\Query\DateQuery;
use Pollora\Query\MetaQuery;
use Pollora\Query\QueryException;

beforeEach(function () {
    WP_Comment_Query::$log = [];
    WP_Comment_Query::$fakeResults = [];
    WP_Comment_Query::$fakeCount = 0;
});

test('CommentQuery: applies no constraint of its own', function () {
    expect(CommentQuery::select()->getArguments())->toBe([]);
});

test('CommentQuery: find maps to comment__in', function () {
    expect(CommentQuery::find(5)->getArguments())->toBe(['comment__in' => [5]])
        ->and(CommentQuery::find([1, 2])->getArguments())->toBe(['comment__in' => [1, 2]]);
});

test('CommentQuery: author and identity filters', function () {
    $args = CommentQuery::select()
        ->authorEmail('a@x.test')->authorUrl('https://x.test')->authorIn([1])->authorNotIn([2])
        ->whereIn([3])->whereNotIn([4])->userId(9)->karma(2)->search('hello')
        ->getArguments();

    expect($args)->toMatchArray([
        'author_email' => 'a@x.test', 'author_url' => 'https://x.test', 'author__in' => [1], 'author__not_in' => [2],
        'comment__in' => [3], 'comment__not_in' => [4], 'user_id' => 9, 'karma' => 2, 'search' => 'hello',
    ]);
});

test('CommentQuery: unapproved comments', function () {
    expect(CommentQuery::select()->includeUnapproved([3, 'me@x.test'])->getArguments())
        ->toMatchArray(['include_unapproved' => [3, 'me@x.test']]);
});

test('CommentQuery: status shortcuts', function () {
    expect(CommentQuery::select()->approved()->getArguments()['status'])->toBe('approve')
        ->and(CommentQuery::select()->pending()->getArguments()['status'])->toBe('hold')
        ->and(CommentQuery::select()->status(['approve', 'spam'])->getArguments()['status'])->toBe(['approve', 'spam']);
});

test('CommentQuery: types', function () {
    $args = CommentQuery::select()->type('pings')->typeIn(['a'])->typeNotIn(['b'])->getArguments();

    expect($args)->toMatchArray(['type' => 'pings', 'type__in' => ['a'], 'type__not_in' => ['b']]);
});

test('CommentQuery: replies', function () {
    $args = CommentQuery::select()->parentId(4)->parentIn([1])->parentNotIn([2])->getArguments();

    expect($args)->toMatchArray(['parent' => 4, 'parent__in' => [1], 'parent__not_in' => [2]])
        ->and(CommentQuery::select()->rootOnly()->getArguments())->toHaveKey('parent', 0);
});

test('CommentQuery: filters on the post', function () {
    $args = CommentQuery::select()
        ->postId(7)->postIn([1])->postNotIn([2])->postAuthor(3)->postAuthorIn([4])->postAuthorNotIn([5])
        ->postName('hello')->postParent(6)->postStatus(['publish', 'private'])->postType('page')
        ->getArguments();

    expect($args)->toMatchArray([
        'post_id' => 7, 'post__in' => [1], 'post__not_in' => [2], 'post_author' => 3,
        'post_author__in' => [4], 'post_author__not_in' => [5], 'post_name' => 'hello', 'post_parent' => 6,
        'post_status' => ['publish', 'private'], 'post_type' => 'page',
    ]);
});

test('CommentQuery: hierarchical', function () {
    expect(CommentQuery::select()->hierarchical()->getArguments()['hierarchical'])->toBe('threaded')
        ->and(CommentQuery::select()->threaded()->getArguments()['hierarchical'])->toBe('threaded')
        ->and(CommentQuery::select()->flat()->getArguments()['hierarchical'])->toBe('flat')
        ->and(CommentQuery::select()->hierarchical(false)->getArguments()['hierarchical'])->toBeFalse();
});

test('CommentQuery: invalid hierarchical mode throws', function () {
    CommentQuery::select()->hierarchical('tree');
})->throws(QueryException::class, 'Invalid hierarchical mode supplied: tree');

test('CommentQuery: fields', function () {
    expect(CommentQuery::select('ids')->getArguments()['fields'])->toBe('ids')
        ->and(CommentQuery::select('*')->getArguments())->not->toHaveKey('fields')
        ->and(CommentQuery::select('all')->getArguments())->not->toHaveKey('fields');
});

test('CommentQuery: invalid fields throws', function () {
    CommentQuery::select('bogus');
})->throws(QueryException::class, 'Invalid fields value supplied: bogus');

test('CommentQuery: orderby is a map of fields and directions', function () {
    $args = CommentQuery::select()->orderBy('comment_author', 'asc')->orderByDate()->getArguments();

    expect($args['orderby'])->toBe(['comment_author' => 'ASC', 'comment_date_gmt' => 'DESC']);
});

test('CommentQuery: order shortcuts', function () {
    expect(CommentQuery::select()->latest()->getArguments()['orderby'])->toBe(['comment_date_gmt' => 'DESC'])
        ->and(CommentQuery::select()->oldest()->getArguments()['orderby'])->toBe(['comment_date_gmt' => 'ASC'])
        ->and(CommentQuery::select()->orderByInclude()->getArguments()['orderby'])->toBe(['comment__in' => 'ASC'])
        ->and(CommentQuery::select()->withoutOrder()->getArguments()['orderby'])->toBe('none');
});

test('CommentQuery: ordering again after withoutOrder starts over', function () {
    expect(CommentQuery::select()->withoutOrder()->latest()->getArguments()['orderby'])->toBe(['comment_date_gmt' => 'DESC']);
});

test('CommentQuery: invalid order direction throws', function () {
    CommentQuery::select()->orderBy('comment_author', 'sideways');
})->throws(QueryException::class, 'Invalid order direction supplied: SIDEWAYS');

test('CommentQuery: limit, offset and paged', function () {
    expect(CommentQuery::select()->limit(5)->offset(10)->paged(2)->getArguments())->toMatchArray(['number' => 5, 'offset' => 10, 'paged' => 2]);
});

test('CommentQuery: cache and found rows', function () {
    $args = CommentQuery::select()->noFoundRows(false)->updateMetaCache(false)->updatePostCache()->cacheDomain('d')->getArguments();

    expect($args)->toMatchArray([
        'no_found_rows' => false, 'update_comment_meta_cache' => false, 'update_comment_post_cache' => true, 'cache_domain' => 'd',
    ]);
});

test('CommentQuery: meta arguments and orderByMeta', function () {
    $args = CommentQuery::select()->metaKey('rating')->metaValue(5)->orderByMeta('rating', 'DESC', true)->getArguments();

    expect($args)->toMatchArray(['meta_key' => 'rating', 'meta_value' => 5])
        ->and($args['orderby'])->toBe(['meta_value_num' => 'DESC']);
});

test('CommentQuery: meta_query reuses the meta builder', function () {
    $args = CommentQuery::select()->metaQuery(function (MetaQuery $q) {
        $q->where($q->meta('rating')->greaterThan(3))->orWhere($q->meta('verified')->equalTo('1'));
    })->getArguments();

    expect($args['meta_query']['relation'])->toBe('OR')
        ->and(array_column(array_filter($args['meta_query'], 'is_array'), 'key'))->toBe(['rating', 'verified']);
});

test('CommentQuery: date_query leaves the column to WP (comment_date)', function () {
    $args = CommentQuery::select()->dateQuery(function (DateQuery $q) {
        $q->where($q->date()->after('2024-01-01'));
    })->getArguments();

    expect($args['date_query'][0])->not->toHaveKey('column');
});

test('CommentQuery: get runs WP_Comment_Query with the arguments', function () {
    $query = CommentQuery::select()->postId(3)->get();

    expect(WP_Comment_Query::$log[0])->toMatchArray(['post_id' => 3])
        ->and($query)->toBeInstanceOf(WP_Comment_Query::class);
});

test('CommentQuery: first', function () {
    $comment = new stdClass;
    WP_Comment_Query::$fakeResults = [$comment, new stdClass];

    expect(CommentQuery::select()->postId(3)->first())->toBe($comment)
        ->and(WP_Comment_Query::$log[0])->toMatchArray(['number' => 1, 'post_id' => 3]);
});

test('CommentQuery: first returns null when nothing matches and ignores paging and threading', function () {
    expect(CommentQuery::select()->offset(5)->paged(3)->threaded()->first())->toBeNull()
        ->and(WP_Comment_Query::$log[0])->not->toHaveKeys(['offset', 'paged', 'hierarchical']);
});

test('CommentQuery: ids', function () {
    WP_Comment_Query::$fakeResults = ['3', 5];

    expect(CommentQuery::select()->limit(20)->ids())->toBe([3, 5])
        ->and(WP_Comment_Query::$log[0])->toMatchArray(['fields' => 'ids', 'number' => 20]);
});

test('CommentQuery: count ignores limit, offset and page', function () {
    WP_Comment_Query::$fakeCount = 42;

    expect(CommentQuery::select()->postId(3)->limit(10)->offset(5)->paged(3)->count())->toBe(42)
        ->and(WP_Comment_Query::$log[0])->toMatchArray(['count' => true, 'post_id' => 3])
        ->and(WP_Comment_Query::$log[0])->not->toHaveKeys(['number', 'offset', 'paged']);
});

test('CommentQuery: exists', function () {
    expect(CommentQuery::select()->exists())->toBeFalse();

    WP_Comment_Query::$fakeResults = [1];

    expect(CommentQuery::select()->exists())->toBeTrue()
        ->and(WP_Comment_Query::$log[1])->toMatchArray(['fields' => 'ids', 'number' => 1]);
});

test('CommentQuery: terminals do not alter the builder', function () {
    $query = CommentQuery::select()->postId(3)->limit(10)->offset(5)->paged(2)->threaded();
    $before = $query->getArguments();

    $query->first();
    $query->ids();
    $query->count();
    $query->exists();

    expect($query->getArguments())->toBe($before)
        ->and($before)->not->toHaveKey('count');
});
