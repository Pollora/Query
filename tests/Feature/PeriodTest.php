<?php

use Pollora\Query\PostQuery;

test('Period: date fields', function () {
    $args = PostQuery::select()
        ->yearMonth(202403)->week(12)->year(2024)->month(3)->day(5)->hour(14)->minute(30)->second(0)
        ->getArguments();

    expect($args)->toMatchArray([
        'm' => 202403, 'w' => 12, 'year' => 2024, 'monthnum' => 3, 'day' => 5, 'hour' => 14, 'minute' => 30, 'second' => 0,
    ]);
});
