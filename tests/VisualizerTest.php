<?php

use Spatie\Period\Visualizer;

it('can visualize periods', function ($options, $expected, $blocks) {
    $visualizer = new Visualizer($options);

    $actual = $visualizer->visualize($blocks);
    $actual = explode("\n", $actual);

    $this->assertEquals($expected, $actual);
})->with('overlapping', 'non_overlapping', 'single_overlaps', 'multiple_overlaps');
