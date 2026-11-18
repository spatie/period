<?php

use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Visualizer;

it('can visualize periods', function ($options, $expected, $blocks) {
    $visualizer = new Visualizer($options);

    $actual = $visualizer->visualize($blocks);
    $actual = explode("\n", $actual);

    expect($actual)->toEqual($expected);
})->with('overlapping', 'non_overlapping', 'single_overlaps', 'multiple_overlaps');

it('can visualize collections and periods of a single unit', function () {
    $visualizer = new Visualizer(['width' => 20]);

    $actual = $visualizer->visualize([
        'A' => Period::make('2018-01-01', '2018-01-20'),
        'B' => new PeriodCollection(Period::make('2018-01-01', '2018-01-05'), Period::make('2018-01-11', '2018-01-15')),
        'C' => Period::make('2018-01-10', '2018-01-10'),
    ]);

    expect(explode("\n", $actual))->toEqual([
        'A    [==================]',
        'B    [===]     [===]     ',
        'C             =          ',
    ]);
});
