<?php

use Spatie\Period\Visualizer;

it('can visualize periods', function ($options, $expected, $blocks) {
    $visualizer = new Visualizer($options);

    $actual = $visualizer->visualize($blocks);
    $actual = explode("\n", $actual);

    expect($actual)->toEqual($expected);
})->with('overlapping', 'non_overlapping', 'single_overlaps', 'multiple_overlaps');
