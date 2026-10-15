<?php

use Pollora\Query\QueryException;

/*
|--------------------------------------------------------------------------
| The documentation is tested
|--------------------------------------------------------------------------
|
| Every example of README.md and docs/*.md that shows the arguments a query builds (or the exception it
| throws) is run, and its output compared with the one written in the page.
|
| When a behaviour changes on purpose, refresh the pages with:
|
|     UPDATE_DOCS=1 vendor/bin/pest --group=docs
|
| and review the diff of the Markdown files.
*/

dataset('documentation examples', function () {
    foreach (docs_files() as $file) {
        foreach (docs_examples($file) as $example) {
            yield basename($file).':'.$example['line'] => [$example];
        }
    }
});

it('documents the arguments and the exceptions that the code produces', function (array $example) {
    if ($example['kind'] === 'exception') {
        try {
            docs_run($example['code']);
            $actual = 'no exception was thrown';
        } catch (QueryException $exception) {
            $actual = 'QueryException: '.$exception->getMessage();
        }
    } else {
        $actual = docs_export(docs_run($example['code']));
    }

    if (getenv('UPDATE_DOCS') && $actual !== $example['expected']) {
        docs_update($example, $actual);

        return;
    }

    expect($actual)->toBe($example['expected']);
})->with('documentation examples')->group('docs');

it('has examples to run', function () {
    expect(iterator_count((function () {
        foreach (docs_files() as $file) {
            yield from docs_examples($file);
        }
    })()))->toBeGreaterThan(50);
})->group('docs');

it('documents every public method of the query classes', function () {
    $text = implode("\n", array_map('file_get_contents', docs_files()));

    $classes = [
        'PostQuery', 'TermQuery', 'UserQuery', 'CommentQuery', 'SiteQuery', 'NetworkQuery', 'Query',
        'MetaQuery', 'TaxQuery', 'DateQuery',
        'QueryBuilder\\MetaQueryBuilder', 'QueryBuilder\\TaxQueryBuilder', 'QueryBuilder\\DateQueryBuilder',
    ];
    $internal = ['getRawArgs', 'extractArgumentFromProperties'];

    $missing = [];

    foreach ($classes as $class) {
        foreach ((new ReflectionClass('Pollora\\Query\\'.$class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();

            if ($method->isConstructor() || in_array($name, $internal, true)) {
                continue;
            }

            if (! str_contains($text, $name.'(')) {
                $missing[] = $class.'::'.$name;
            }
        }
    }

    expect($missing)->toBe([]);
})->group('docs');

it('only links to files and sections that exist', function () {
    $anchors = function (string $file): array {
        $found = [];

        foreach (explode("\n", file_get_contents($file)) as $line) {
            if (preg_match('/^#{1,6}\s+(.*)$/', $line, $heading)) {
                $title = strtolower(str_replace(['`', '*'], '', trim($heading[1])));
                $found[] = str_replace(' ', '-', preg_replace('/[^\w\s\-]/u', '', $title));
            }
        }

        return $found;
    };

    $broken = [];

    foreach (docs_files() as $file) {
        preg_match_all('/\]\(([^)\s]+)\)/', file_get_contents($file), $links);

        foreach ($links[1] as $link) {
            if (preg_match('~^(https?:|mailto:)~', $link)) {
                continue;
            }

            [$path, $anchor] = array_pad(explode('#', $link, 2), 2, null);
            $target = $path === '' ? $file : realpath(dirname($file).'/'.$path);

            if ($target === false || ! file_exists($target)) {
                $broken[] = basename($file).' -> '.$link.' (no such file)';
            } elseif ($anchor !== null && str_ends_with($target, '.md') && ! in_array($anchor, $anchors($target), true)) {
                $broken[] = basename($file).' -> '.$link.' (no such section)';
            }
        }
    }

    expect($broken)->toBe([]);
})->group('docs');
