<?php

use Spatie\Period\Period;

/**
 * A        [=======]
 * B                   [======]
 *
 * DIFF     [=======]  [======]
 */
it('diff with no overlap', function () {
    $a = Period::make('2020-01-01', '2020-01-15');
    $b = Period::make('2020-02-01', '2020-02-15');

    $diff = $a->diffSymmetric($b);

    expect($diff)->toHaveCount(2);
    expect($diff[0]->equals($a))->toBeTrue();
    expect($diff[1]->equals($b))->toBeTrue();
});

/**
 * A        [=======]
 * B              [=======]
 *
 * DIFF     [====]   [====]
 */
it('diff right', function () {
    $a = Period::make('2020-01-01', '2020-01-31');

    $b = Period::make('2020-01-15', '2020-02-15');

    $diff = $a->diffSymmetric($b);

    expect($diff)->toHaveCount(2);
    expect($diff[0]->equals(Period::make('2020-01-01', '2020-01-14')))->toBeTrue();
    expect($diff[1]->equals(Period::make('2020-02-01', '2020-02-15')))->toBeTrue();
});

/**
 * A             [=======]
 * B        [=======]
 *
 * DIFF     [====]   [====]
 */
it('diff left', function () {
    $a = Period::make('2020-01-15', '2020-02-15');
    $b = Period::make('2020-01-01', '2020-01-31');

    $diff = $a->diffSymmetric($b);

    expect($diff)->toHaveCount(2);
    expect($diff[0]->equals(Period::make('2020-01-01', '2020-01-14')))->toBeTrue();
    expect($diff[1]->equals(Period::make('2020-02-01', '2020-02-15')))->toBeTrue();
});

/**
 * A        [=============================]
 * B            [========]
 *
 * DIFF     [==]          [===============]
 */
it('diff within', function () {
    $a = Period::make('2020-01-01', '2020-01-31');
    $b = Period::make('2020-01-10', '2020-01-15');

    $diffs = $a->diffSymmetric($b);

    expect($diffs)->toHaveCount(2);
    expect($diffs[0]->equals(Period::make('2020-01-01', '2020-01-09')))->toBeTrue();
    expect($diffs[1]->equals(Period::make('2020-01-16', '2020-01-31')))->toBeTrue();
});

/**
 * A            [========]
 * B        [=============================]
 *
 * DIFF     [==]          [===============]
 */
it('diff within reverse', function () {
    $a = Period::make('2020-01-10', '2020-01-15');
    $b = Period::make('2020-01-01', '2020-01-31');

    $diffs = $a->diffSymmetric($b);

    expect($diffs)->toHaveCount(2);
    expect($diffs[0]->equals(Period::make('2020-01-01', '2020-01-09')))->toBeTrue();
    expect($diffs[1]->equals(Period::make('2020-01-16', '2020-01-31')))->toBeTrue();
});
