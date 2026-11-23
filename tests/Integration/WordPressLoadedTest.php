<?php

declare(strict_types=1);

/**
 * Proves the suite runs against a real WordPress and not against doubles: if the bootstrap did not load it, or if a
 * Brain Monkey stub were defining these functions, one of these tests fails.
 */
it('has loaded WordPress', function (): void {
    expect(function_exists('register_post_meta'))->toBeTrue()
        ->and(defined('ABSPATH'))->toBeTrue()
        ->and(get_bloginfo('name') !== '')->toBeTrue();
});

it('talks to the database of the installed site', function (): void {
    global $wpdb;

    expect($wpdb)->toBeInstanceOf(wpdb::class)
        ->and(get_option('siteurl'))->toBeString()
        ->and(get_option('siteurl') !== '')->toBeTrue()
        ->and(is_blog_installed())->toBeTrue();
});

it('fires the real hooks', function (): void {
    $seen = 0;
    add_action('pollora_harness_probe', function () use (&$seen): void {
        $seen++;
    });

    do_action('pollora_harness_probe');

    expect($seen)->toBe(1)
        ->and(has_action('pollora_harness_probe'))->toBeTrue();
});

it('registers post meta, which fails without WordPress', function (): void {
    expect(register_post_meta('post', 'pollora_harness_meta', ['type' => 'string', 'single' => true, 'show_in_rest' => true]))->toBeTrue()
        ->and(registered_meta_key_exists('post', 'pollora_harness_meta', 'post'))->toBeTrue();
});
