<?php

/**
 * Support for the tests that run the examples of the documentation (tests/Docs).
 *
 * An example is a ```php block, followed either by
 *   - a "`WP_Query` receives:" / "`getArguments()` returns:" line and a ```php block holding the expected arguments,
 *   - or, inside the block, a last "// QueryException: message" line.
 */

/**
 * @return array<string> Absolute paths of the Markdown files that are checked.
 */
function docs_files(): array
{
    $root = dirname(__DIR__, 2);

    return [$root.'/README.md', $root.'/CHANGELOG.md', ...glob($root.'/docs/*.md')];
}

/**
 * Prints a value as short-array PHP, the way the documentation shows the arguments.
 */
function docs_export(mixed $value, int $level = 0): string
{
    if (is_array($value)) {
        if ($value === []) {
            return '[]';
        }

        $pad = str_repeat('    ', $level + 1);
        $next = 0;
        $lines = [];

        foreach ($value as $key => $item) {
            $label = '';

            if (is_int($key) && $key === $next) {
                $next++;
            } else {
                $label = (is_int($key) ? $key : "'".addcslashes($key, "'\\")."'").' => ';
                $next = is_int($key) ? $key + 1 : $next;
            }

            $lines[] = $pad.$label.docs_export($item, $level + 1).',';
        }

        return "[\n".implode("\n", $lines)."\n".str_repeat('    ', $level).']';
    }

    return match (true) {
        is_string($value) => "'".addcslashes($value, "'\\")."'",
        is_bool($value) => $value ? 'true' : 'false',
        $value === null => 'null',
        default => var_export($value, true),
    };
}

/**
 * @return array<int, array{file: string, line: int, kind: string, code: string, expected: string, block: string}>
 */
function docs_examples(string $file): array
{
    $markdown = file_get_contents($file);
    $examples = [];

    $found = [
        'arguments' => '~(?<block>```php\n(?<code>(?:(?!```).)*?)\n```\n\n`[^`\n]+` (?:receives|returns):\n\n```php\n(?<expected>(?:(?!```).)*?)\n```)~s',
        'exception' => '~(?<block>```php\n(?<code>(?:(?!```).)*?)\n// (?<expected>\w*Exception: [^\n]*)\n```)~s',
    ];

    foreach ($found as $kind => $pattern) {
        preg_match_all($pattern, $markdown, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            $examples[] = [
                'file' => $file,
                'line' => substr_count(substr($markdown, 0, $match['block'][1]), "\n") + 1,
                'kind' => $kind,
                'code' => $match['code'][0],
                'expected' => $match['expected'][0],
                'block' => $match['block'][0],
            ];
        }
    }

    usort($examples, fn (array $a, array $b): int => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);

    return $examples;
}

/**
 * Runs the code of an example. Examples are one expression: a trailing `->get()` is read as `->getArguments()`
 * so that no WordPress query runs.
 */
function docs_run(string $code): mixed
{
    $expression = rtrim(trim($code), ';');
    $expression = preg_replace('/->get\(\)$/', '->getArguments()', $expression);

    $imports = 'use Pollora\Query\{PostQuery, TermQuery, UserQuery, CommentQuery, SiteQuery, NetworkQuery, Query, MetaQuery, TaxQuery, DateQuery, QueryException};';

    return eval($imports.' return '.$expression.';');
}

/**
 * Rewrites the expected output of an example in its file (UPDATE_DOCS=1).
 */
function docs_update(array $example, string $actual): void
{
    $contents = file_get_contents($example['file']);
    $rewritten = $example['kind'] === 'exception'
        ? str_replace($example['block'], preg_replace('~// \w*Exception: [^\n]*\n```$~', '// '.$actual."\n```", $example['block']), $contents)
        : str_replace($example['block'], substr($example['block'], 0, -strlen($example['expected']) - 4).$actual."\n```", $contents);

    file_put_contents($example['file'], $rewritten);
}
