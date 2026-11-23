<?php

declare(strict_types=1);

use Pollora\Query\CommentQuery;
use Pollora\Query\DateQuery;
use Pollora\Query\MetaQuery;
use Pollora\Query\PostQuery;
use Pollora\Query\TaxQuery;
use Pollora\Query\TermQuery;
use Pollora\Query\UserQuery;

/**
 * The builders against the real query classes and a real database: the unit suite records the arguments given to a
 * double, so it cannot tell that WordPress reads them as the builder means (a meta value cast, a hierarchical
 * tax query, a count that ignores the limit...).
 *
 * The posts live in a post type of their own, so the posts of the site never match; everything is removed after each test.
 */
beforeEach(function (): void {
    $GLOBALS['wpint'] = ['posts' => [], 'terms' => [], 'comments' => [], 'users' => []];

    register_post_type('wpint_item', ['public' => true]);
    register_taxonomy('wpint_genre', 'wpint_item', ['hierarchical' => true, 'public' => true]);
});

afterEach(function (): void {
    require_once ABSPATH.'wp-admin/includes/user.php';

    foreach ($GLOBALS['wpint']['comments'] as $id) {
        wp_delete_comment($id, true);
    }

    foreach ($GLOBALS['wpint']['posts'] as $id) {
        wp_delete_post($id, true);
    }

    foreach ($GLOBALS['wpint']['terms'] as $id) {
        wp_delete_term($id, 'wpint_genre');
    }

    foreach ($GLOBALS['wpint']['users'] as $id) {
        wp_delete_user($id);
    }

    unregister_taxonomy('wpint_genre');
    unregister_post_type('wpint_item');
});

/**
 * Creates a published post of the `wpint_item` type with its meta and terms.
 *
 * @param  array<string, mixed>  $meta
 * @param  list<int>  $terms
 */
function wpintItem(string $title, array $meta = [], array $terms = [], string $date = '2026-03-15 10:00:00'): int
{
    $id = wp_insert_post([
        'post_type' => 'wpint_item',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_date' => $date,
        'post_date_gmt' => get_gmt_from_date($date),
        'meta_input' => $meta,
    ]);
    expect($id)->toBeInt()->toBeGreaterThan(0);
    $GLOBALS['wpint']['posts'][] = $id;

    // Not `tax_input`: wp_insert_post() only honours it for a user who may assign the terms.
    if ($terms !== []) {
        wp_set_object_terms($id, $terms, 'wpint_genre');
    }

    return $id;
}

function wpintGenre(string $slug, int $parent = 0): int
{
    $term = wp_insert_term(ucfirst($slug), 'wpint_genre', ['slug' => $slug, 'parent' => $parent]);
    expect($term)->toBeArray();
    $GLOBALS['wpint']['terms'][] = $term['term_id'];

    return $term['term_id'];
}

it('compares numeric meta values as numbers, which a string comparison gets wrong', function (): void {
    $five = wpintItem('Five', ['wpint_rank' => 5]);
    $ten = wpintItem('Ten', ['wpint_rank' => 10]);
    $hundred = wpintItem('Hundred', ['wpint_rank' => 100]);

    $above = fn (int|float $value): array => PostQuery::select()->postType('wpint_item')
        ->metaQuery(fn (MetaQuery $query) => $query->where($query->meta('wpint_rank')->greaterThan($value)))
        ->ids();

    expect($above(9))->toEqualCanonicalizing([$ten, $hundred])
        ->and($above(9.5))->toEqualCanonicalizing([$ten, $hundred])
        ->and($above(100))->toBe([])
        ->and(PostQuery::select()->postType('wpint_item')
            ->metaQuery(fn (MetaQuery $query) => $query->where($query->meta('wpint_rank')->between(5, 10)))
            ->ids())->toEqualCanonicalizing([$five, $ten]);
});

it('runs every meta value type the builder detects, against real casts', function (): void {
    $flagged = wpintItem('Flagged', ['wpint_flag' => 1, 'wpint_when' => '2026-02-10', 'wpint_at' => '2026-02-10 08:00:00', 'wpint_price' => '9.99', 'wpint_label' => 'Spring sale']);
    $plain = wpintItem('Plain', ['wpint_flag' => 0, 'wpint_when' => '2026-09-01', 'wpint_at' => '2026-09-01 18:30:00', 'wpint_price' => '19.50', 'wpint_label' => 'Autumn']);
    $bare = wpintItem('Bare');

    $ids = static function (callable $where): array {
        global $wpdb;

        $ids = PostQuery::select()->postType('wpint_item')->metaQuery(fn (MetaQuery $query) => $query->where($where($query)))->ids();
        // A cast MySQL refuses would not throw: WordPress would return nothing and keep the error.
        expect($wpdb->last_error)->toBe('');

        return $ids;
    };

    expect($ids(fn (MetaQuery $q) => $q->meta('wpint_flag')->equalTo(1)))->toBe([$flagged])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_when')->between('2026-01-01', '2026-06-30')))->toBe([$flagged])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_at')->greaterThan('2026-06-01 00:00:00')))->toBe([$plain])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_price')->ofType('DECIMAL(10,2)')->lessThan(10.0)))->toBe([$flagged])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_label')->like('sale')))->toBe([$flagged])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_label')->in(['Autumn', 'Winter'])))->toBe([$plain])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_label')->exists()))->toEqualCanonicalizing([$flagged, $plain])
        ->and($ids(fn (MetaQuery $q) => $q->meta('wpint_label')->notExists()))->toBe([$bare]);
});

it('combines meta clauses with OR through the relation WordPress expects', function (): void {
    $a = wpintItem('A', ['wpint_status' => 'active']);
    $b = wpintItem('B', ['wpint_status' => 'archived', 'wpint_highlight' => 1]);
    wpintItem('C', ['wpint_status' => 'archived', 'wpint_highlight' => 0]);

    $ids = PostQuery::select()->postType('wpint_item')
        ->metaQuery(fn (MetaQuery $query) => $query
            ->where($query->meta('wpint_status')->equalTo('active'))
            ->orWhere($query->meta('wpint_highlight')->equalTo(1)))
        ->ids();

    expect($ids)->toEqualCanonicalizing([$a, $b]);
});

it('follows the hierarchy of a taxonomy, or not, as asked', function (): void {
    $rock = wpintGenre('rock');
    $punk = wpintGenre('punk', $rock);
    $jazz = wpintGenre('jazz');
    $onRock = wpintItem('On rock', terms: [$rock]);
    $onPunk = wpintItem('On punk', terms: [$punk]);
    $onJazz = wpintItem('On jazz', terms: [$jazz]);
    $onBoth = wpintItem('On both', terms: [$rock, $jazz]);

    $by = fn (callable $build): array => PostQuery::select()->postType('wpint_item')
        ->taxQuery(fn (TaxQuery $query) => $query->where($build($query)))
        ->ids();

    expect($by(fn (TaxQuery $q) => $q->taxonomy('wpint_genre')->contains(['rock'])->searchByTermSlug()))
        ->toEqualCanonicalizing([$onRock, $onPunk, $onBoth])
        ->and($by(fn (TaxQuery $q) => $q->taxonomy('wpint_genre')->contains(['rock'])->searchByTermSlug()->excludeChildren()))
        ->toEqualCanonicalizing([$onRock, $onBoth])
        ->and($by(fn (TaxQuery $q) => $q->taxonomy('wpint_genre')->containsAll(['rock', 'jazz'])->searchByTermSlug()->excludeChildren()))
        ->toBe([$onBoth])
        ->and($by(fn (TaxQuery $q) => $q->taxonomy('wpint_genre')->notContains(['jazz'])->searchByTermSlug()))
        ->toEqualCanonicalizing([$onRock, $onPunk])
        ->and($by(fn (TaxQuery $q) => $q->taxonomy('wpint_genre')->contains([$jazz])->searchById()))
        ->toEqualCanonicalizing([$onJazz, $onBoth]);
});

it('filters on the dates of a post with the boundaries the builder documents', function (): void {
    $march = wpintItem('March', date: '2026-03-15 10:00:00');
    $june = wpintItem('June', date: '2026-06-20 23:30:00');
    $october = wpintItem('October', date: '2026-10-01 00:00:00');

    $by = fn (callable $build): array => PostQuery::select()->postType('wpint_item')
        ->dateQuery(fn (DateQuery $query) => $query->where($build($query)))
        ->ids();

    expect($by(fn (DateQuery $q) => $q->date()->created()->after('2026-04-01')))->toEqualCanonicalizing([$june, $october])
        ->and($by(fn (DateQuery $q) => $q->date()->created()->before('2026-04-01')))->toBe([$march])
        ->and($by(fn (DateQuery $q) => $q->date()->created()->between('2026-03-01', '2026-06-30')))->toEqualCanonicalizing([$march, $june]);
});

it('returns the first post, the ids and the totals the terminal methods promise', function (): void {
    $old = wpintItem('Old', date: '2026-01-01 09:00:00');
    $middle = wpintItem('Middle', date: '2026-02-01 09:00:00');
    $new = wpintItem('New', date: '2026-03-01 09:00:00');

    $items = fn () => PostQuery::select()->postType('wpint_item');

    expect($items()->latest()->first())->toBeInstanceOf(WP_Post::class)
        ->and($items()->latest()->fields('ids')->first())->toBe($new)
        ->and($items()->oldest()->fields('ids')->first())->toBe($old)
        ->and($items()->latest()->ids())->toBe([$new, $middle, $old])
        ->and($items()->latest()->limit(2)->ids())->toBe([$new, $middle])
        ->and($items()->latest()->limit(2)->offset(2)->ids())->toBe([$old])
        ->and($items()->limit(1)->count())->toBe(3)
        ->and($items()->exists())->toBeTrue()
        ->and($items()->postSlug('no-such-item')->exists())->toBeFalse()
        ->and($items()->postSlug('no-such-item')->first())->toBeNull();
});

it('queries terms, users and comments through their own WordPress classes', function (): void {
    $rock = wpintGenre('rock');
    $punk = wpintGenre('punk', $rock);
    $jazz = wpintGenre('jazz');
    $post = wpintItem('Commented', terms: [$punk]);

    $approved = wp_insert_comment(['comment_post_ID' => $post, 'comment_content' => 'Great', 'comment_approved' => '1', 'comment_author' => 'Fan']);
    $pending = wp_insert_comment(['comment_post_ID' => $post, 'comment_content' => 'Maybe', 'comment_approved' => '0', 'comment_author' => 'Unknown']);
    $GLOBALS['wpint']['comments'] = [$approved, $pending];

    $author = wp_insert_user(['user_login' => 'wpint_writer', 'user_pass' => wp_generate_password(), 'user_email' => 'wpint_writer@example.com', 'role' => 'author']);
    $GLOBALS['wpint']['users'][] = $author;

    $genres = fn () => TermQuery::select()->taxonomy('wpint_genre')->hideEmpty(false);

    expect($genres()->rootOnly()->slugs())->toEqualCanonicalizing(['rock', 'jazz'])
        ->and($genres()->count())->toBe(3)
        ->and($genres()->limit(1)->count())->toBe(3)
        ->and($genres()->childOf($rock)->ids())->toBe([$punk])
        ->and($genres()->hideEmpty()->ids())->toEqualCanonicalizing([$punk, $rock])
        ->and(CommentQuery::select()->postId($post)->approved()->ids())->toBe([$approved])
        ->and(CommentQuery::select()->postId($post)->pending()->ids())->toBe([$pending])
        ->and(CommentQuery::select()->postId($post)->status('all')->count())->toBe(2)
        ->and(CommentQuery::select()->postId($post)->status('all')->limit(1)->count())->toBe(2)
        ->and(UserQuery::select()->roleIn(['author'])->ids())->toBe([$author])
        ->and(UserQuery::select()->roleIn(['author'])->count())->toBe(1)
        ->and(UserQuery::select()->login('wpint_writer')->exists())->toBeTrue()
        ->and(UserQuery::select()->login('wpint_nobody')->exists())->toBeFalse();
});
