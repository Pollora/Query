<?php
/**
 * Parses a string into variables to be stored in an array.
 *
 * @since 2.2.1
 *
 * @param  string  $input_string The string to be parsed.
 * @param  array  $result       Variables will be stored in this array.
 */
function wp_parse_str(string $input_string, array &$result): void
{
    parse_str($input_string, $result);
}

/**
 * Merges user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for both string or array
 * to be merged into another array.
 *
 * @since 2.2.0
 * @since 2.3.0 `$args` can now also be an object.
 *
 * @param  string|array|object  $args     Value to merge with $defaults.
 * @param  array  $defaults Optional. Array that serves as the defaults.
 *                                      Default empty array.
 * @return array Merged user defined values with defaults.
 */
function wp_parse_args(string|array|object $args, array $defaults = []): array
{
    if (is_object($args)) {
        $parsed_args = get_object_vars($args);
    } elseif (is_array($args)) {
        $parsed_args = &$args;
    } else {
        wp_parse_str($args, $parsed_args);
    }

    if (is_array($defaults) && $defaults) {
        return array_merge($defaults, $parsed_args);
    }

    return $parsed_args;
}

if (! class_exists('WP_Query')) {
    /**
     * Minimal WP_Query double: records the arguments and returns the posts set up by the test.
     */
    class WP_Query
    {
        /** @var array<array> */
        public static array $log = [];

        public static array $fakePosts = [];

        public static int $fakeFound = 0;

        public array $query_vars;

        public array $posts;

        public int $found_posts;

        public function __construct(array $args = [])
        {
            self::$log[] = $args;
            $this->query_vars = $args;
            $this->posts = self::$fakePosts;
            $this->found_posts = self::$fakeFound;
        }
    }
}
